<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Competition;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Get accessible competition IDs for current user.
     */
    protected function getAccessibleCompetitionIds(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        return PicController::getManagedCompetitionIds($user);
    }

    /**
     * 1. Display Attendance Index & Live Scanner
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $managedCompIds = $this->getAccessibleCompetitionIds();

        // Get competitions for filter dropdown
        $competitionsQuery = Competition::query();
        if ($user->role === 'pic_lomba') {
            $competitionsQuery->whereIn('id', $managedCompIds);
        }
        $competitions = $competitionsQuery->orderBy('name', 'asc')->get();

        // Handle direct scan query param from smartphone camera (?scan=TLT-...)
        $scanFlash = null;
        if ($request->filled('scan')) {
            $scanFlash = $this->processDirectScan($request->query('scan'), $managedCompIds);
        }

        // Build main query
        $selectedCompId = $request->query('competition_id');
        $attendanceStatus = $request->query('status', 'all'); // all, hadir, belum_hadir
        $search = trim($request->query('search', ''));

        $query = Registration::with(['competition', 'members', 'user', 'attendedBy'])
            ->where('status', 'verified');

        if ($user->role === 'pic_lomba') {
            $query->whereIn('competition_id', $managedCompIds);
        }

        if (! empty($selectedCompId)) {
            $query->where('competition_id', $selectedCompId);
        }

        if ($attendanceStatus === 'hadir') {
            $query->where('is_attended', true);
        } elseif ($attendanceStatus === 'belum_hadir') {
            $query->where('is_attended', false);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_code', 'like', "%{$search}%")
                    ->orWhere('participant_number', 'like', "%{$search}%")
                    ->orWhere('team_name', 'like', "%{$search}%")
                    ->orWhere('institution_name', 'like', "%{$search}%")
                    ->orWhereHas('members', function ($m) use ($search) {
                        $m->where('full_name', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    });
            });
        }

        // Clone query for stats
        $statsBase = Registration::where('status', 'verified');
        if ($user->role === 'pic_lomba') {
            $statsBase->whereIn('competition_id', $managedCompIds);
        }
        if (! empty($selectedCompId)) {
            $statsBase->where('competition_id', $selectedCompId);
        }

        $totalRegistered = (clone $statsBase)->count();
        $totalAttended = (clone $statsBase)->where('is_attended', true)->count();
        $totalUnattended = $totalRegistered - $totalAttended;
        $attendancePercentage = $totalRegistered > 0 ? round(($totalAttended / $totalRegistered) * 100, 1) : 0;

        $registrations = $query->orderBy('is_attended', 'desc')
            ->orderBy('attended_at', 'desc')
            ->orderBy('participant_number', 'asc')
            ->paginate(50)
            ->withQueryString();

        $appSettings = AppSetting::pluck('value', 'key')->toArray();

        return view('admin.attendance.index', compact(
            'competitions',
            'registrations',
            'totalRegistered',
            'totalAttended',
            'totalUnattended',
            'attendancePercentage',
            'selectedCompId',
            'attendanceStatus',
            'search',
            'scanFlash',
            'appSettings'
        ));
    }

    /**
     * 2. AJAX Endpoint for QR Code Scanner & Barcode Gun
     */
    public function scan(Request $request): JsonResponse
    {
        $rawCode = trim($request->input('code', ''));
        if (empty($rawCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode presensi tidak boleh kosong.',
            ], 422);
        }

        $cleanCode = $this->extractCleanCode($rawCode);
        $managedCompIds = $this->getAccessibleCompetitionIds();
        $user = Auth::user();

        $registration = Registration::with(['competition', 'members', 'user', 'attendedBy'])
            ->where(function ($q) use ($cleanCode) {
                $q->where('registration_code', $cleanCode)
                    ->orWhere('participant_number', $cleanCode);
            })
            ->first();

        if (! $registration) {
            return response()->json([
                'success' => false,
                'message' => "Data peserta dengan kode '{$cleanCode}' tidak ditemukan dalam sistem.",
            ], 404);
        }

        // PIC access check
        if ($user->role === 'pic_lomba' && ! in_array($registration->competition_id, $managedCompIds)) {
            return response()->json([
                'success' => false,
                'message' => "Anda tidak memiliki akses sebagai PIC untuk cabang lomba {$registration->competition->name}.",
            ], 403);
        }

        // Verified status check
        if ($registration->status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => "Pendaftaran peserta ini belum terverifikasi (Status: {$registration->status}). Hanya peserta terverifikasi yang dapat dipresensi.",
            ], 422);
        }

        // Check if already attended
        if ($registration->is_attended) {
            return response()->json([
                'success' => true,
                'already_attended' => true,
                'message' => 'Peserta ini sudah melakukan presensi sebelumnya pada '.($registration->attended_at ? $registration->attended_at->format('d/m/Y H:i') : '-').' WIB oleh '.($registration->attendedBy?->name ?? 'Panitia').'.',
                'participant' => $this->formatParticipantData($registration),
            ]);
        }

        // Record attendance
        $registration->is_attended = true;
        $registration->attended_at = Carbon::now();
        $registration->attended_by = $user->id;
        $registration->save();

        return response()->json([
            'success' => true,
            'already_attended' => false,
            'message' => "Presensi BERHASIL! Kehadiran {$registration->display_name} telah tercatat resmi.",
            'participant' => $this->formatParticipantData($registration),
        ]);
    }

    /**
     * 3. Toggle Attendance Status Manually
     */
    public function toggle(Request $request, $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $managedCompIds = $this->getAccessibleCompetitionIds();

        $registration = Registration::with(['competition'])->findOrFail($id);

        if ($user->role === 'pic_lomba' && ! in_array($registration->competition_id, $managedCompIds)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke cabang lomba ini.'], 403);
            }

            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke cabang lomba ini.');
        }

        if ($registration->is_attended) {
            $registration->is_attended = false;
            $registration->attended_at = null;
            $registration->attended_by = null;
            $registration->save();
            $msg = "Status kehadiran {$registration->display_name} dibatalkan (Belum Hadir).";
        } else {
            $registration->is_attended = true;
            $registration->attended_at = Carbon::now();
            $registration->attended_by = $user->id;
            $registration->save();
            $msg = "Status kehadiran {$registration->display_name} berhasil ditandai Hadir.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_attended' => $registration->is_attended,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * 4. Printable Attendance Sheet (Lembar Daftar Hadir Resmi)
     */
    public function printReport(Request $request): View
    {
        $user = Auth::user();
        $managedCompIds = $this->getAccessibleCompetitionIds();

        $compId = $request->query('competition_id');
        if (empty($compId)) {
            $competition = $user->role === 'pic_lomba'
                ? Competition::whereIn('id', $managedCompIds)->first()
                : Competition::first();
        } else {
            $competition = Competition::findOrFail($compId);
        }

        if ($user->role === 'pic_lomba' && (! $competition || ! in_array($competition->id, $managedCompIds))) {
            abort(403, 'Anda tidak memiliki akses ke cabang lomba ini.');
        }

        $registrations = Registration::with(['members', 'user', 'attendedBy'])
            ->where('competition_id', $competition->id)
            ->where('status', 'verified')
            ->orderBy('is_attended', 'desc')
            ->orderBy('participant_number', 'asc')
            ->get();

        $appSettings = AppSetting::pluck('value', 'key')->toArray();

        return view('admin.attendance.print', compact('competition', 'registrations', 'appSettings'));
    }

    /**
     * Helper: Extract clean registration code from raw scan string or URL
     */
    protected function extractCleanCode(string $raw): string
    {
        $raw = trim($raw);

        // If it's a URL, extract scan or q param
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            $parsed = parse_url($raw);
            if (! empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (! empty($queryParams['scan'])) {
                    return trim($queryParams['scan']);
                }
                if (! empty($queryParams['q'])) {
                    return trim($queryParams['q']);
                }
            }
        }

        // If format contains prefix like "TALENTA:ATTEND:..."
        if (str_contains($raw, 'TALENTA:ATTEND:')) {
            return trim(str_replace('TALENTA:ATTEND:', '', $raw));
        }

        return $raw;
    }

    /**
     * Helper: Format participant data for JSON response
     */
    protected function formatParticipantData(Registration $reg): array
    {
        return [
            'id' => $reg->id,
            'registration_code' => $reg->registration_code,
            'participant_number' => $reg->participant_number ?: '-',
            'name' => $reg->display_name,
            'institution' => $reg->display_school ?: ($reg->institution_name ?: '-'),
            'competition' => $reg->competition->name ?? '-',
            'sub_category' => $reg->sub_category ?: '-',
            'attended_at' => $reg->attended_at ? $reg->attended_at->format('d/m/Y H:i:s') : Carbon::now()->format('d/m/Y H:i:s'),
            'attended_by' => $reg->attendedBy?->name ?? (Auth::user()?->name ?? 'Panitia'),
        ];
    }

    /**
     * Helper: Process direct scan from smartphone URL query param
     */
    protected function processDirectScan(string $rawCode, array $managedCompIds): array
    {
        $cleanCode = $this->extractCleanCode($rawCode);
        $user = Auth::user();

        $registration = Registration::with(['competition', 'members', 'user', 'attendedBy'])
            ->where(function ($q) use ($cleanCode) {
                $q->where('registration_code', $cleanCode)
                    ->orWhere('participant_number', $cleanCode);
            })
            ->first();

        if (! $registration) {
            return [
                'type' => 'error',
                'message' => "Data peserta dengan kode '{$cleanCode}' tidak ditemukan.",
            ];
        }

        if ($user->role === 'pic_lomba' && ! in_array($registration->competition_id, $managedCompIds)) {
            return [
                'type' => 'error',
                'message' => "Anda tidak memiliki hak akses sebagai PIC untuk cabang {$registration->competition->name}.",
            ];
        }

        if ($registration->status !== 'verified') {
            return [
                'type' => 'error',
                'message' => "Pendaftaran belum terverifikasi (Status: {$registration->status}).",
            ];
        }

        if ($registration->is_attended) {
            return [
                'type' => 'info',
                'message' => "Peserta {$registration->display_name} ({$registration->participant_number}) sudah hadir sebelumnya pada ".($registration->attended_at ? $registration->attended_at->format('d/m/Y H:i') : '-').' WIB.',
                'participant' => $this->formatParticipantData($registration),
            ];
        }

        $registration->is_attended = true;
        $registration->attended_at = Carbon::now();
        $registration->attended_by = $user->id;
        $registration->save();

        return [
            'type' => 'success',
            'message' => "Presensi BERHASIL! {$registration->display_name} ({$registration->participant_number}) telah ditandai Hadir.",
            'participant' => $this->formatParticipantData($registration),
        ];
    }
}
