<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Competition;
use App\Models\DrawAllocation;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\User;
use App\Services\WablasNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PicController extends Controller
{
    /**
     * Get IDs of all competitions managed by user (both as primary PIC and sector/category PIC in AppSetting)
     */
    public static function getManagedCompetitionIds($user): array
    {
        if ($user->role === 'superadmin') {
            return Competition::pluck('id')->toArray();
        }

        $extraCodes = [];
        $bltPics = array_filter([
            AppSetting::get('blt_pic_tunggal_pa'),
            AppSetting::get('blt_pic_tunggal_pi'),
            AppSetting::get('blt_pic_ganda_pa'),
            AppSetting::get('blt_pic_ganda_pi'),
        ]);
        if (in_array($user->id, $bltPics) || in_array((string) $user->id, $bltPics)) {
            $extraCodes[] = 'BLT';
        }

        $tmjPics = array_filter([
            AppSetting::get('tmj_pic_tunggal_pa'),
            AppSetting::get('tmj_pic_tunggal_pi'),
        ]);
        if (in_array($user->id, $tmjPics) || in_array((string) $user->id, $tmjPics)) {
            $extraCodes[] = 'TMJ';
        }

        $mtqPics = array_filter([
            AppSetting::get('mtq_pic_pa'),
            AppSetting::get('mtq_pic_pi'),
        ]);
        if (in_array($user->id, $mtqPics) || in_array((string) $user->id, $mtqPics)) {
            $extraCodes[] = 'MTQ';
        }

        $popPics = array_filter([
            AppSetting::get('pop_pic_pa'),
            AppSetting::get('pop_pic_pi'),
        ]);
        if (in_array($user->id, $popPics) || in_array((string) $user->id, $popPics)) {
            $extraCodes[] = 'POP';
        }

        return Competition::where(function ($q) use ($user, $extraCodes) {
            $q->where('pic_id', $user->id)
                ->orWhereHas('pics', function ($sub) use ($user) {
                    $sub->where('users.id', $user->id);
                });
            if (! empty($extraCodes)) {
                $q->orWhereIn('code', $extraCodes);
            }
        })->pluck('id')->toArray();
    }

    protected function authorizeCompetitionManagement($user, $competitionId): void
    {
        if (! in_array($competitionId, self::getManagedCompetitionIds($user))) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola cabang lomba ini.');
        }
    }

    public function dashboard()
    {
        $user = Auth::user();
        $competitionIds = self::getManagedCompetitionIds($user);

        // Get competitions managed by this PIC (or all if superadmin)
        $competitions = Competition::with(['category', 'registrations.members'])
            ->whereIn('id', $competitionIds)
            ->get();

        $allRegistrations = Registration::with(['competition.category', 'members', 'user', 'invoice'])
            ->whereIn('competition_id', $competitionIds)
            ->latest()
            ->get();

        $totalMembers = $allRegistrations->flatMap->members;
        $totalPa = $totalMembers->where('gender', 'L')->count();
        $totalPi = $totalMembers->where('gender', 'P')->count();

        $stats = [
            'total_competitions' => $competitions->count(),
            'total_registrations' => $allRegistrations->count(),
            'pending_verifications' => $allRegistrations->where('status', 'pending')->count(),
            'pending_registrations' => $allRegistrations->where('status', 'pending')->count(),
            'verified_registrations' => $allRegistrations->where('status', 'verified')->count(),
            'revision_registrations' => $allRegistrations->where('status', 'revision')->count(),
            'rejected_registrations' => $allRegistrations->where('status', 'rejected')->count(),
            'drawn_participants' => $allRegistrations->whereNotNull('draw_number')->count(),
            'total_pa' => $totalPa,
            'total_pi' => $totalPi,
        ];

        $categories = Category::all();

        return view('pic.dashboard', compact('user', 'competitions', 'stats', 'allRegistrations', 'categories'));
    }

    public function printParticipantsPdf(Request $request)
    {
        $user = Auth::user();
        $competitionId = $request->query('competition_id', 'all');
        $status = $request->query('status', 'all');
        $genderFilter = $request->query('gender', 'all');

        $managedCompIds = self::getManagedCompetitionIds($user);
        $query = Registration::with(['competition.category', 'competition.pic', 'members', 'user'])
            ->whereIn('competition_id', $managedCompIds)
            ->when($competitionId !== 'all', function ($q) use ($competitionId) {
                $q->where('competition_id', $competitionId);
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($genderFilter !== 'all', function ($q) use ($genderFilter) {
                $q->whereHas('members', function ($mq) use ($genderFilter) {
                    $mq->where('gender', $genderFilter);
                });
            });

        $registrations = $query->get();

        // Group into printable separate pages
        $pages = [];
        $competitions = $registrations->groupBy('competition_id');

        foreach ($competitions as $compId => $compRegs) {
            $comp = $compRegs->first()->competition;
            $picName = (Auth::check() && Auth::user()->isPic()) ? Auth::user()->name : ($comp->pic->name ?? Auth::user()->name ?? 'Panitia Pelaksana');
            $picPosition = !empty($comp->pic?->position) ? $comp->pic->position : 'Panitia Pelaksana';
            $isBuluTangkis = ($comp->code === 'BLT' || stripos($comp->name, 'bulu tangkis') !== false || stripos($comp->name, 'badminton') !== false);

            if ($isBuluTangkis) {
                // 1. Tunggal Categories (Kat A, Kat B, Kat C)
                $bltCategories = [
                    'kat_a' => 'Kategori A (Kelas 1–2)',
                    'kat_b' => 'Kategori B (Kelas 3–4)',
                    'kat_c' => 'Kategori C (Kelas 5–6)',
                ];

                foreach ($bltCategories as $catKey => $catLabel) {
                    $catRegs = $compRegs->filter(function ($r) use ($catKey) {
                        if ($r->isGanda()) {
                            return false;
                        }
                        if ($catKey === 'kat_a') {
                            return $r->isKatA();
                        }
                        if ($catKey === 'kat_b') {
                            return $r->isKatB();
                        }
                        if ($catKey === 'kat_c') {
                            return $r->isKatC();
                        }

                        return false;
                    });

                    if ($catRegs->isNotEmpty()) {
                        $paRegs = $catRegs->filter(fn ($r) => $r->primary_gender === 'L')->sortBy(fn ($r) => $r->draw_number ?: 9999)->values();
                        $piRegs = $catRegs->filter(fn ($r) => $r->primary_gender === 'P')->sortBy(fn ($r) => $r->draw_number ?: 9999)->values();

                        if ($paRegs->isNotEmpty()) {
                            $pages[] = [
                                'competition' => $comp,
                                'competition_name' => $comp->name,
                                'sub_group_title' => '👦 KELOMPOK PUTRA (PA)',
                                'sector_title' => $catLabel.' - TUNGGAL PUTRA',
                                'gender_badge_class' => 'bg-blue-100 text-blue-900',
                                'registrations' => $paRegs,
                                'pic_name' => $picName,
                                'pic_position' => $picPosition,
                            ];
                        }
                        if ($piRegs->isNotEmpty()) {
                            $pages[] = [
                                'competition' => $comp,
                                'competition_name' => $comp->name,
                                'sub_group_title' => '👧 KELOMPOK PUTRI (PI)',
                                'sector_title' => $catLabel.' - TUNGGAL PUTRI',
                                'gender_badge_class' => 'bg-rose-100 text-rose-900',
                                'registrations' => $piRegs,
                                'pic_name' => $picName,
                                'pic_position' => $picPosition,
                            ];
                        }
                    }
                }

                // 2. Ganda Categories (Direct Ganda Putra & Ganda Putri)
                $gandaAllRegs = $compRegs->filter(function ($r) {
                    $targetStr = strtolower(($r->target_class ?? '').' '.($r->sub_category ?? '').' '.($r->match_type ?? ''));

                    return stripos($targetStr, 'ganda') !== false || $r->members->count() > 1;
                });

                if ($gandaAllRegs->isNotEmpty()) {
                    $gandaPa = $gandaAllRegs->filter(fn ($r) => $r->primary_gender === 'L' || stripos($r->match_type, 'Putra') !== false || stripos($r->match_type, 'PA') !== false)->sortBy(fn ($r) => $r->draw_number ?: 9999)->values();
                    $gandaPi = $gandaAllRegs->filter(fn ($r) => $r->primary_gender === 'P' || stripos($r->match_type, 'Putri') !== false || stripos($r->match_type, 'PI') !== false)->sortBy(fn ($r) => $r->draw_number ?: 9999)->values();

                    if ($gandaPa->isNotEmpty()) {
                        $pages[] = [
                            'competition' => $comp,
                            'competition_name' => $comp->name,
                            'sub_group_title' => '👥 KELOMPOK GANDA PUTRA (PA)',
                            'sector_title' => 'GANDA PUTRA (PA) - SEMUA KELAS',
                            'gender_badge_class' => 'bg-blue-100 text-blue-900',
                            'registrations' => $gandaPa,
                            'pic_name' => $picName,
                            'pic_position' => $picPosition,
                        ];
                    }
                    if ($gandaPi->isNotEmpty()) {
                        $pages[] = [
                            'competition' => $comp,
                            'competition_name' => $comp->name,
                            'sub_group_title' => '👥 KELOMPOK GANDA PUTRI (PI)',
                            'sector_title' => 'GANDA PUTRI (PI) - SEMUA KELAS',
                            'gender_badge_class' => 'bg-rose-100 text-rose-900',
                            'registrations' => $gandaPi,
                            'pic_name' => $picName,
                            'pic_position' => $picPosition,
                        ];
                    }
                }
            } else {
                // General Competition: Separate Page 1 (PA) and Page 2 (PI)
                $paRegs = $compRegs->filter(fn ($r) => $r->primary_gender === 'L')->sortBy(fn ($r) => $r->draw_number ?: 9999)->values();
                $piRegs = $compRegs->filter(fn ($r) => $r->primary_gender === 'P')->sortBy(fn ($r) => $r->draw_number ?: 9999)->values();
                $otherRegs = $compRegs->filter(fn ($r) => ! in_array($r->primary_gender, ['L', 'P']))->sortBy(fn ($r) => $r->draw_number ?: 9999)->values();

                if ($paRegs->isNotEmpty() || ($piRegs->isEmpty() && $otherRegs->isEmpty())) {
                    $pages[] = [
                        'competition' => $comp,
                        'competition_name' => $comp->name,
                        'sub_group_title' => '👦 KELOMPOK PUTRA (PA)',
                        'sector_title' => $comp->category->name ?? 'Tingkat SD/MI',
                        'gender_badge_class' => 'bg-blue-100 text-blue-900',
                        'registrations' => $paRegs,
                        'pic_name' => $picName,
                        'pic_position' => $picPosition,
                    ];
                }
                if ($piRegs->isNotEmpty()) {
                    $pages[] = [
                        'competition' => $comp,
                        'competition_name' => $comp->name,
                        'sub_group_title' => '👧 KELOMPOK PUTRI (PI)',
                        'sector_title' => $comp->category->name ?? 'Tingkat SD/MI',
                        'gender_badge_class' => 'bg-rose-100 text-rose-900',
                        'registrations' => $piRegs,
                        'pic_name' => $picName,
                        'pic_position' => $picPosition,
                    ];
                }
                if ($otherRegs->isNotEmpty()) {
                    $pages[] = [
                        'competition' => $comp,
                        'competition_name' => $comp->name,
                        'sub_group_title' => '👥 KELOMPOK BEREGU / CAMPURAN',
                        'sector_title' => $comp->category->name ?? 'Tingkat SD/MI',
                        'gender_badge_class' => 'bg-purple-100 text-purple-900',
                        'registrations' => $otherRegs,
                        'pic_name' => $picName,
                        'pic_position' => $picPosition,
                    ];
                }
            }
        }

        return view('pic.print-participants-pdf', compact('pages'));
    }

    public function exportParticipantsExcel(Request $request)
    {
        $user = Auth::user();
        $competitionId = $request->query('competition_id', 'all');
        $status = $request->query('status', 'all');
        $genderFilter = $request->query('gender', 'all');

        $managedCompIds = self::getManagedCompetitionIds($user);
        $query = Registration::with(['competition.category', 'members', 'user'])
            ->whereIn('competition_id', $managedCompIds)
            ->when($competitionId !== 'all', function ($q) use ($competitionId) {
                $q->where('competition_id', $competitionId);
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($genderFilter !== 'all', function ($q) use ($genderFilter) {
                $q->whereHas('members', function ($mq) use ($genderFilter) {
                    $mq->where('gender', $genderFilter);
                });
            });

        $registrations = $query->get();

        // Sort into 1 single sheet: by Competition -> by Gender (PA then PI) -> by Draw/Participant No
        $sorted = $registrations->sort(function ($a, $b) {
            if ($a->competition_id !== $b->competition_id) {
                return strcmp($a->competition->name ?? '', $b->competition->name ?? '');
            }
            $genderOrder = ['L' => 1, 'P' => 2, 'M' => 3];
            $gA = $genderOrder[$a->primary_gender] ?? 4;
            $gB = $genderOrder[$b->primary_gender] ?? 4;
            if ($gA !== $gB) {
                return $gA <=> $gB;
            }

            return strcmp($a->participant_number ?? '', $b->participant_number ?? '');
        });

        $filename = 'DATA_PESERTA_TALENTA_2026_'.date('Ymd_His').'.xls';

        return response()->streamDownload(function () use ($sorted) {
            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: Calibri, sans-serif; }
                    th { background-color: #064E3B; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; }
                    td { border: 1px solid #CCCCCC; vertical-align: middle; }
                    .center { text-align: center; }
                    .bold { font-weight: bold; }
                    .pa { background-color: #EFF6FF; }
                    .pi { background-color: #FFF1F2; }
                </style>
            </head>
            <body>
                <table border="1">
                    <tr>
                        <th colspan="13" style="font-size: 16pt; background-color: #047857; text-align: center; height: 35px;">
                            DATA NOMINATIF PESERTA RESMI TALENTA 2026 - MTsN 1 BLITAR
                        </th>
                    </tr>
                    <tr>
                        <th colspan="13" style="font-size: 10pt; background-color: #D1FAE5; color: #065F46; text-align: center;">
                            Diurutkan Berdasarkan Cabang Lomba & Kelompok Gender (Putra / Putri) dalam 1 Sheet
                        </th>
                    </tr>
                    <tr>
                        <th>No</th>
                        <th>Kode Registrasi</th>
                        <th>No. Peserta</th>
                        <th>No. Undian</th>
                        <th>Nama Peserta / Atlet</th>
                        <th>NISN</th>
                        <th>Gender (PA/PI)</th>
                        <th>Cabang Lomba</th>
                        <th>Kategori / Sektor</th>
                        <th>Asal Sekolah / Madrasah</th>
                        <th>Nama Official</th>
                        <th>No. HP Official</th>
                        <th>Status Keabsahan</th>
                    </tr>';

            $no = 1;
            foreach ($sorted as $reg) {
                $firstMember = $reg->members->first();
                $isGanda = $reg->members->count() > 1;
                $gender = $reg->primary_gender;
                $genderClass = ($gender === 'L') ? 'pa' : (($gender === 'P') ? 'pi' : '');
                $genderLabel = ($gender === 'L') ? 'Putra (PA)' : (($gender === 'P') ? 'Putri (PI)' : 'Ganda / Campuran');

                echo '<tr class="'.$genderClass.'">
                    <td class="center">'.$no++.'</td>
                    <td class="center">'.htmlspecialchars($reg->registration_code).'</td>
                    <td class="center bold">'.htmlspecialchars($reg->participant_number ?: '-').'</td>
                    <td class="center bold">'.htmlspecialchars($reg->draw_number ? '#'.$reg->draw_number : '-').'</td>
                    <td class="bold">'.htmlspecialchars($reg->display_name).'</td>
                    <td class="center">'.htmlspecialchars($firstMember?->nisn ?: '-').'</td>
                    <td class="center bold">'.htmlspecialchars($genderLabel).'</td>
                    <td>'.htmlspecialchars($reg->competition->name ?? '-').'</td>
                    <td>'.htmlspecialchars(($reg->target_class ?: '').' '.($reg->sub_category ?: '')).'</td>
                    <td>'.htmlspecialchars($reg->institution_name).'</td>
                    <td>'.htmlspecialchars($reg->official_name ?: '-').'</td>
                    <td class="center">'.htmlspecialchars($reg->official_phone ?: '-').'</td>
                    <td class="center bold">'.ucfirst($reg->status).'</td>
                </tr>';
            }

            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function participants(Request $request, $competition_id)
    {
        $user = Auth::user();
        $competition = Competition::with('category')->findOrFail($competition_id);

        $this->authorizeCompetitionManagement($user, $competition->id);

        $statusFilter = $request->input('status', 'all');

        $registrations = Registration::with(['members', 'user', 'scores', 'invoice', 'competition.category'])
            ->where('competition_id', $competition->id)
            ->when($statusFilter !== 'all', function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pic.participants', compact('competition', 'registrations', 'statusFilter'));
    }

    public function verifyParticipant(Request $request, $registration_id)
    {
        $registration = Registration::with(['competition', 'invoice.registrations'])->findOrFail($registration_id);
        $user = Auth::user();
        $this->authorizeCompetitionManagement($user, $registration->competition_id);

        $validated = $request->validate([
            'status' => ['required', 'in:verified,rejected,revision'],
            'verification_notes' => ['nullable', 'string'],
        ]);

        $registration->status = $validated['status'];
        $registration->verification_notes = $validated['verification_notes'];
        $registration->verified_by = $user->id;
        $registration->verified_at = now();

        // If verified, generate official participant number if not exists
        if ($validated['status'] === 'verified' && empty($registration->participant_number)) {
            $registration->participant_number = $registration->generateParticipantNumber();
        }

        $registration->save();

        ActivityLog::record(
            $validated['status'] === 'verified' ? 'VERIFY_SUCCESS' : 'VERIFY_REJECT',
            ($validated['status'] === 'verified' ? 'Memverifikasi sah' : 'Menolak')." peserta '{$registration->display_name}' ({$registration->registration_code}) pada cabang {$registration->competition->name}".($validated['verification_notes'] ? ". Catatan: {$validated['verification_notes']}" : ''),
            $user,
            $validated['status'] === 'verified' ? 'success' : 'warning'
        );

        // Auto-sync invoice status if part of collective registration
        if ($registration->invoice_id && $registration->invoice) {
            $invoice = $registration->invoice;
            $allRegs = $invoice->registrations;

            if ($allRegs->every(fn ($r) => $r->status === 'verified')) {
                $invoice->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                    'verified_by' => $user->id,
                ]);
            } elseif ($validated['status'] === 'rejected' && $invoice->status === 'pending') {
                $invoice->update([
                    'status' => 'rejected',
                    'verified_at' => now(),
                    'verified_by' => $user->id,
                    'rejection_reason' => $validated['verification_notes'] ?? 'Ditolak saat verifikasi berkas/pembayaran.',
                ]);
            }
        }

        // Trigger Auto WhatsApp Notification: Status Verifikasi (Sah, Revisi, atau Tolak)
        try {
            $registration->loadMissing(['members', 'user', 'competition']);
            $memberNisns = $registration->members->pluck('nisn')->filter()->unique();
            $nisn = $memberNisns->isNotEmpty() ? $memberNisns->implode(' / ') : ($registration->user?->nisn ?? '-');
            $targetPhones = $registration->recipient_phones;

            if ($validated['status'] === 'verified') {
                WablasNotificationService::sendAutoNotification('registration_verified', [
                    'phone' => $targetPhones,
                    'nama_peserta' => $registration->pure_name,
                    'nisn' => $nisn,
                    'nama_sekolah' => $registration->institution_name,
                    'cabang_lomba' => $registration->competition->name,
                    'no_peserta' => $registration->participant_number ?: $registration->registration_code,
                    'kode_pendaftaran' => $registration->registration_code,
                    'link_scoreboard' => url('/'),
                    'link_login' => route('login'),
                ]);
            } elseif ($validated['status'] === 'revision') {
                WablasNotificationService::sendAutoNotification('registration_revision', [
                    'phone' => $targetPhones,
                    'nama_peserta' => $registration->pure_name,
                    'nisn' => $nisn,
                    'nama_sekolah' => $registration->institution_name,
                    'cabang_lomba' => $registration->competition->name,
                    'kode_pendaftaran' => $registration->registration_code,
                    'catatan_verifikasi' => $validated['verification_notes'] ?: 'Mohon periksa kembali berkas persyaratan lomba Anda.',
                    'link_login' => route('peserta.registration.detail', $registration->id),
                ]);
            } elseif ($validated['status'] === 'rejected') {
                WablasNotificationService::sendAutoNotification('registration_rejected', [
                    'phone' => $targetPhones,
                    'nama_peserta' => $registration->pure_name,
                    'nisn' => $nisn,
                    'nama_sekolah' => $registration->institution_name,
                    'cabang_lomba' => $registration->competition->name,
                    'kode_pendaftaran' => $registration->registration_code,
                    'catatan_verifikasi' => $validated['verification_notes'] ?: 'Berkas pendaftaran tidak memenuhi kriteria lomba.',
                    'link_login' => route('login'),
                ]);
            }
        } catch (\Throwable $e) {
            // Non-blocking
            \Illuminate\Support\Facades\Log::error("Gagal mengirim WhatsApp status verifikasi ({$validated['status']}): " . $e->getMessage());
        }

        return back()->with('success', 'Status pendaftaran '.$registration->registration_code.' berhasil diubah menjadi: '.ucfirst($validated['status']));
    }

    public function updateParticipantData(Request $request, $registration_id)
    {
        $registration = Registration::with(['competition', 'members'])->findOrFail($registration_id);
        $user = Auth::user();

        $this->authorizeCompetitionManagement($user, $registration->competition_id);

        $validated = $request->validate([
            'institution_name' => ['required', 'string', 'max:255'],
            'official_name' => ['nullable', 'string', 'max:255'],
            'official_phone' => ['nullable', 'string', 'max:20'],
            'team_name' => ['nullable', 'string', 'max:255'],
            'target_class' => ['nullable', 'string', 'max:50'],
            'match_type' => ['nullable', 'string', 'max:50'],
            'participant_number' => ['nullable', 'string', 'max:50'],
            'draw_number' => ['nullable', 'integer'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.id' => ['nullable', 'integer'],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.school_name' => ['nullable', 'string', 'max:255'],
            'members.*.nisn' => ['nullable', 'string', 'max:20'],
            'members.*.gender' => ['required', 'in:L,P'],
            'members.*.birth_place' => ['nullable', 'string', 'max:100'],
            'members.*.birth_date' => ['nullable', 'date'],
            'members.*.phone' => ['nullable', 'string', 'max:20'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $finalInstitutionName = trim($validated['institution_name']);

        // Synchronize school name for single participant or ganda
        if (count($validated['members']) === 1 && ! empty($validated['members'][0]['school_name'])) {
            $firstSchool = trim($validated['members'][0]['school_name']);
            // Prioritize the member's school name so that editing "Asal Sekolah Siswa" updates the institution
            $finalInstitutionName = $firstSchool;
        } elseif (count($validated['members']) > 1) {
            $memberSchools = array_unique(array_filter(array_map('trim', array_column($validated['members'], 'school_name'))));
            // If member schools are multiple and different, and institution_name wasn't custom-changed
            if (count($memberSchools) > 1 && $validated['institution_name'] === $registration->getOriginal('institution_name')) {
                $finalInstitutionName = implode(' / ', $memberSchools);
            }
        }

        $registration->institution_name = $finalInstitutionName;
        $registration->official_name = $validated['official_name'];
        $registration->official_phone = $validated['official_phone'];
        $registration->team_name = $validated['team_name'] ?? null;
        $registration->target_class = $validated['target_class'] ?? null;
        if (! empty($validated['match_type'])) {
            $registration->match_type = $validated['match_type'];
        }
        $registration->participant_number = $validated['participant_number'] ?? null;
        $registration->draw_number = ! empty($validated['draw_number']) ? $validated['draw_number'] : null;
        if ($request->has('chosen_song')) {
            $registration->chosen_song = $request->input('chosen_song') ?: null;
        }

        if ($registration->competition && $registration->competition->code === 'BLT') {
            if ($registration->match_type && stripos($registration->match_type, 'ganda') !== false) {
                $registration->target_class = 'Ganda (Semua Kelas)';
                $registration->sub_category = $registration->match_type;
            } else {
                if (empty($registration->target_class) || stripos($registration->target_class, 'ganda') !== false) {
                    $registration->target_class = 'Kategori A (Kelas 1 - 2)';
                }
                if (! empty($registration->match_type)) {
                    $registration->sub_category = $registration->target_class.' - '.$registration->match_type;
                }
            }
        } elseif ($registration->competition && $registration->competition->code === 'TMJ') {
            if (empty($registration->target_class)) {
                $registration->target_class = 'Kategori A (Kelas 1 - 3)';
            }
            if (! empty($registration->match_type)) {
                $registration->sub_category = $registration->target_class.' - '.$registration->match_type;
            } else {
                $registration->sub_category = $registration->target_class;
            }
        } elseif ($registration->competition && in_array($registration->competition->code, ['MTQ', 'POP'])) {
            if (! empty($registration->match_type)) {
                $registration->sub_category = $registration->match_type;
            }
        }

        if ($request->hasFile('document_file')) {
            $registration->document_file = $request->file('document_file')->store('documents', 'public');
            AdminSettingsController::ensurePublicStorageSync($registration->document_file);
        }
        if ($request->hasFile('payment_proof')) {
            $registration->payment_proof = $request->file('payment_proof')->store('payments', 'public');
            AdminSettingsController::ensurePublicStorageSync($registration->payment_proof);
        }

        $registration->save();

        // Update member records
        foreach ($validated['members'] as $idx => $mData) {
            if (! empty($mData['id'])) {
                $member = RegistrationMember::where('registration_id', $registration->id)->find($mData['id']);
                if ($member) {
                    $mGender = $mData['gender'];
                    // Auto-sync gender if single/individual sector was selected
                    if ($registration->competition && in_array($registration->competition->code, ['TMJ', 'BLT', 'MTQ', 'POP'])) {
                        if ($registration->match_type && stripos($registration->match_type, 'ganda') === false) {
                            if (stripos($registration->match_type, 'Putri') !== false || stripos($registration->match_type, '(PI)') !== false) {
                                $mGender = 'P';
                            } elseif (stripos($registration->match_type, 'Putra') !== false || stripos($registration->match_type, '(PA)') !== false) {
                                $mGender = 'L';
                            }
                        }
                    }

                    $mSchool = ! empty($mData['school_name']) ? trim($mData['school_name']) : (count($validated['members']) === 1 ? $finalInstitutionName : null);
                    $member->update([
                        'full_name' => $mData['full_name'],
                        'school_name' => $mSchool,
                        'nisn' => $mData['nisn'] ?? null,
                        'gender' => $mGender,
                        'birth_place' => $mData['birth_place'] ?? null,
                        'birth_date' => $mData['birth_date'] ?? null,
                        'phone' => $mData['phone'] ?? null,
                    ]);
                }
            }
        }

        return back()->with('success', 'Data pendaftaran '.$registration->display_name.' ('.$registration->registration_code.') berhasil diperbarui oleh Admin.');
    }

    public function unverifyParticipant($registration_id)
    {
        $registration = Registration::with(['competition', 'invoice.registrations'])->findOrFail($registration_id);
        $user = Auth::user();

        $this->authorizeCompetitionManagement($user, $registration->competition_id);

        $registration->status = 'pending';
        $registration->verified_at = null;
        $registration->verified_by = null;
        $registration->verification_notes = 'Verifikasi dibatalkan oleh Panitia/Admin. Data pendaftaran dibuka kembali untuk diedit.';
        $registration->save();

        if ($registration->invoice_id && $registration->invoice) {
            $registration->invoice->update([
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
            ]);
        }

        ActivityLog::record(
            'UNVERIFY',
            "Membatalkan verifikasi pendaftaran '{$registration->display_name}' ({$registration->registration_code}) kembali ke Pending",
            $user,
            'warning'
        );

        return back()->with('success', 'Verifikasi pendaftaran '.$registration->registration_code.' telah dibatalkan. Status otomatis kembali menjadi Menunggu dan peserta dapat mengedit data di akunnya.');
    }

    public function deleteParticipant($registration_id)
    {
        $registration = Registration::with(['competition'])->findOrFail($registration_id);
        $user = Auth::user();

        $this->authorizeCompetitionManagement($user, $registration->competition_id);

        $code = $registration->registration_code;
        $name = $registration->display_name;

        $registration->scores()->delete();
        $registration->drawAllocation()->delete();
        $registration->members()->delete();
        $registration->delete();

        ActivityLog::record(
            'DELETE_PARTICIPANT',
            "Menghapus data pendaftaran peserta '{$name}' ({$code}) secara permanen",
            $user,
            'danger'
        );

        return back()->with('success', 'Data pendaftaran '.$name.' ('.$code.') berhasil dihapus secara permanen.');
    }

    /**
     * Store manual participant registration by Admin or PIC
     */
    public function storeParticipant(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'competition_id' => ['required', 'exists:competitions,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'institution_name' => ['required', 'string', 'max:255'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'match_type' => ['nullable', 'string', 'max:50'],
            'target_class' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,verified'],
            'payment_method' => ['required', 'in:tunai,transfer'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'verification_notes' => ['nullable', 'string', 'max:255'],
            'ignore_quota' => ['nullable'],
            // Optional Member 2 for Ganda BLT
            'member2_name' => ['nullable', 'string', 'max:255'],
            'member2_nisn' => ['nullable', 'string', 'max:20'],
            'member2_school' => ['nullable', 'string', 'max:255'],
        ], [
            'competition_id.required' => 'Cabang lomba wajib dipilih.',
            'full_name.required' => 'Nama lengkap peserta wajib diisi.',
            'gender.required' => 'Jenis kelamin peserta wajib dipilih.',
            'institution_name.required' => 'Nama asal sekolah/madrasah wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih (Tunai / Transfer).',
            'payment_proof.mimes' => 'Format berkas bukti pembayaran harus berupa JPG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran berkas bukti pembayaran maksimal 5MB.',
        ]);

        $competition = Competition::findOrFail($validated['competition_id']);
        $this->authorizeCompetitionManagement($user, $competition->id);

        // Harmonize gender & category/match_type based on competition rules
        $gender = $validated['gender'];
        $matchType = $validated['match_type'] ?? null;
        $targetClass = $validated['target_class'] ?? null;
        $subCategory = null;

        if ($competition->code === 'BLT') {
            $isGanda = ! empty($matchType) && stripos($matchType, 'ganda') !== false;
            $isPutri = ! empty($matchType) && (stripos($matchType, 'putri') !== false || stripos($matchType, '(pi)') !== false);
            $isPutra = ! empty($matchType) && (stripos($matchType, 'putra') !== false || stripos($matchType, '(pa)') !== false);

            if ($isPutri) {
                $gender = 'P';
            } elseif ($isPutra) {
                $gender = 'L';
            }

            if ($isGanda) {
                $targetClass = 'Ganda (Semua Kelas)';
                $subCategory = $matchType;
            } else {
                if (empty($targetClass) || stripos($targetClass, 'ganda') !== false) {
                    $targetClass = 'Kategori A (Kelas 1 - 2)';
                }
                if (! empty($matchType)) {
                    $subCategory = $targetClass.' - '.$matchType;
                }
            }
        } elseif ($competition->code === 'TMJ') {
            $isPutri = ! empty($matchType) && (stripos($matchType, 'putri') !== false || stripos($matchType, '(pi)') !== false);
            $isPutra = ! empty($matchType) && (stripos($matchType, 'putra') !== false || stripos($matchType, '(pa)') !== false);

            if ($isPutri) {
                $gender = 'P';
            } elseif ($isPutra) {
                $gender = 'L';
            }

            if (! empty($targetClass) && ! empty($matchType)) {
                $subCategory = $targetClass.' - '.$matchType;
            } elseif (! empty($matchType)) {
                $subCategory = $matchType;
            }
        } elseif (in_array($competition->code, ['MTQ', 'POP'])) {
            $subCategory = ($gender === 'P') ? 'Putri (PI)' : 'Putra (PA)';
            $matchType = $subCategory;
        } elseif (! empty($targetClass) && ! empty($matchType)) {
            $subCategory = $targetClass.' - '.$matchType;
        } elseif (! empty($matchType)) {
            $subCategory = $matchType;
        }

        // Check quota if ignore_quota is not checked
        if (! $request->boolean('ignore_quota')) {
            if ($competition->quota > 0 && ! in_array($competition->code, ['BLT', 'TMJ'])) {
                $currentTotal = Registration::where('competition_id', $competition->id)
                    ->whereIn('status', ['pending', 'verified'])
                    ->count();
                if ($currentTotal >= $competition->quota) {
                    return back()->with('error', "Kuota pendaftaran untuk {$competition->name} sudah penuh ({$competition->quota} peserta). Centang opsi 'Abaikan Kuota' jika ini merupakan dispensasi khusus.")->withInput();
                }
            }
        }

        // Find or create User for the participant so they can login to portal if needed
        $nisnClean = ! empty($validated['nisn']) ? trim($validated['nisn']) : null;
        $participantUser = null;
        $isNewUser = false;
        if ($nisnClean) {
            $participantUser = User::where('nisn', $nisnClean)->first();
        }
        if (! $participantUser) {
            $randomSuffix = rand(100, 999);
            $autoNisn = $nisnClean ?: ('MNL'.date('ymd').$randomSuffix);
            $email = $autoNisn.'@peserta.talenta';
            if (User::where('email', $email)->exists()) {
                $email = $autoNisn.'_'.rand(10, 99).'@peserta.talenta';
            }
            $participantUser = User::create([
                'name' => $validated['full_name'],
                'nisn' => $nisnClean,
                'email' => $email,
                'password' => Hash::make($nisnClean ?: 'talenta2026'),
                'role' => 'peserta',
                'phone' => $validated['phone'] ?? null,
                'institution_name' => $validated['institution_name'],
                'account_type' => 'pendaftar',
                'status' => 'active',
            ]);
            $isNewUser = true;
        }

        // Prevent duplicate NISN in same competition / sector
        $nisnsToCheck = [];
        if ($nisnClean) {
            $nisnsToCheck[] = [
                'nisn' => $nisnClean,
                'name' => $validated['full_name'],
                'role' => 'Pemain 1 / Peserta Utama',
            ];
        }
        $member2NisnClean = ! empty($validated['member2_nisn']) ? trim($validated['member2_nisn']) : null;
        if ($member2NisnClean) {
            $nisnsToCheck[] = [
                'nisn' => $member2NisnClean,
                'name' => $validated['member2_name'] ?? 'Pemain 2',
                'role' => 'Pemain 2',
            ];
        }

        $isBltGanda = ($competition->code === 'BLT') && (! empty($matchType) && stripos($matchType, 'ganda') !== false);

        foreach ($nisnsToCheck as $item) {
            $existingMemberQuery = RegistrationMember::where('nisn', $item['nisn'])
                ->whereHas('registration', function ($q) use ($competition, $isBltGanda) {
                    $q->where('competition_id', $competition->id)
                        ->whereIn('status', ['pending', 'verified']);

                    if ($competition->code === 'BLT') {
                        if ($isBltGanda) {
                            $q->where(function ($sub) {
                                $sub->where('match_type', 'like', '%ganda%')
                                    ->orWhere('target_class', 'like', '%ganda%')
                                    ->orWhere('sub_category', 'like', '%ganda%');
                            });
                        } else {
                            $q->where(function ($sub) {
                                $sub->where(function ($s) {
                                    $s->whereNull('match_type')
                                        ->orWhere('match_type', 'not like', '%ganda%');
                                })->where(function ($s) {
                                    $s->whereNull('target_class')
                                        ->orWhere('target_class', 'not like', '%ganda%');
                                })->where(function ($s) {
                                    $s->whereNull('sub_category')
                                        ->orWhere('sub_category', 'not like', '%ganda%');
                                });
                            });
                        }
                    }
                });

            $existingMember = $existingMemberQuery->first();
            if ($existingMember) {
                $sectorText = ($competition->code === 'BLT') ? ('sektor '.($isBltGanda ? 'Ganda' : 'Tunggal').' ') : '';

                return back()->with('error', "Peserta ({$item['name']}) dengan NISN '{$item['nisn']}' sudah terdaftar pada {$sectorText}cabang {$competition->name}.")->withInput();
            }
        }

        // Store payment proof file
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payments', 'public');
            AdminSettingsController::ensurePublicStorageSync($paymentProofPath);
        }

        $regCode = 'REG-'.date('Y').'-'.$competition->code.'-'.strtoupper(Str::random(5));

        $teamName = null;
        if (! empty($validated['member2_name'])) {
            $teamName = $validated['full_name'].' / '.$validated['member2_name'];
        }

        $status = $validated['status'];
        $methodTag = $validated['payment_method'] === 'tunai' ? '[LUNAS TUNAI PIC: '.$user->name.']' : '[TRANSFER BANK: '.$user->name.']';
        $userNotes = ! empty($validated['verification_notes']) ? ' - '.$validated['verification_notes'] : '';
        $notes = $methodTag.$userNotes;

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $participantUser->id,
            'registration_code' => $regCode,
            'team_name' => $teamName,
            'sub_category' => $subCategory,
            'chosen_song' => $request->input('chosen_song') ?: null,
            'target_class' => $targetClass,
            'match_type' => $matchType,
            'institution_name' => $validated['institution_name'],
            'official_name' => $user->name,
            'official_phone' => $validated['phone'] ?? $user->phone,
            'status' => $status,
            'payment_proof' => $paymentProofPath,
            'verified_at' => $status === 'verified' ? now() : null,
            'verified_by' => $status === 'verified' ? $user->id : null,
            'verification_notes' => $notes,
        ]);

        // Generate participant number if verified
        if ($status === 'verified') {
            $registration->generateParticipantNumber();
        }

        // Create Member 1
        RegistrationMember::create([
            'registration_id' => $registration->id,
            'full_name' => $validated['full_name'],
            'school_name' => $validated['institution_name'],
            'nisn' => $nisnClean,
            'gender' => $gender,
            'phone' => $validated['phone'] ?? null,
            'role_in_team' => ! empty($validated['member2_name']) ? 'Pemain 1' : 'Peserta Utama',
        ]);

        // Create Member 2 if Ganda
        if (! empty($validated['member2_name'])) {
            RegistrationMember::create([
                'registration_id' => $registration->id,
                'full_name' => $validated['member2_name'],
                'school_name' => $validated['member2_school'] ?: $validated['institution_name'],
                'nisn' => ! empty($validated['member2_nisn']) ? trim($validated['member2_nisn']) : null,
                'gender' => $gender,
                'role_in_team' => 'Pemain 2',
            ]);
        }

        ActivityLog::record(
            'MANUAL_REGISTRATION',
            "Mendaftarkan peserta baru '{$validated['full_name']}' secara manual pada cabang {$competition->name}".($status === 'verified' ? ' (Langsung Terverifikasi/Lunas Tunai)' : ' (Status: Pending)'),
            $user,
            'success'
        );

        // Trigger Auto WhatsApp Notifications for Manual Registration
        try {
            $registration->loadMissing(['members', 'user', 'competition']);
            $targetPhones = $registration->recipient_phones;
            if (empty($targetPhones) && ! empty($validated['phone'])) {
                $targetPhones = [$validated['phone']];
            }

            if (! empty($targetPhones)) {
                // 1. Notifikasi Akun Baru (jika dibuatkan akun baru)
                if ($isNewUser) {
                    WablasNotificationService::sendAutoNotification('account_created', [
                        'phone' => $targetPhones,
                        'nama_peserta' => $validated['full_name'],
                        'nisn' => $participantUser->nisn ?: $participantUser->email,
                        'nama_sekolah' => $validated['institution_name'],
                        'link_login' => route('login'),
                    ]);
                }

                // 2. Notifikasi Pendaftaran / Verifikasi ke Peserta & Official
                $memberNisns = $registration->members->pluck('nisn')->filter()->unique();
                $regNisn = $memberNisns->isNotEmpty() ? $memberNisns->implode(' / ') : ($nisnClean ?: ($participantUser->nisn ?: '-'));

                if ($status === 'verified') {
                    WablasNotificationService::sendAutoNotification('registration_verified', [
                        'phone' => $targetPhones,
                        'nama_peserta' => $validated['full_name'],
                        'nisn' => $regNisn,
                        'nama_sekolah' => $validated['institution_name'],
                        'cabang_lomba' => $competition->name,
                        'no_peserta' => $registration->participant_number ?: $registration->registration_code,
                        'kode_pendaftaran' => $registration->registration_code,
                        'link_scoreboard' => url('/'),
                        'link_login' => route('login'),
                    ]);
                } else {
                    WablasNotificationService::sendAutoNotification('registration_submitted', [
                        'phone' => $targetPhones,
                        'nama_peserta' => $validated['full_name'],
                        'nisn' => $regNisn,
                        'nama_sekolah' => $validated['institution_name'],
                        'cabang_lomba' => $competition->name,
                        'kode_pendaftaran' => $registration->registration_code,
                        'link_login' => route('login'),
                    ]);
                }
            }

            // 3. Notifikasi Alert ke Seluruh Petugas PIC Lomba
            WablasNotificationService::notifyPicNewRegistration($registration);

        } catch (\Throwable $e) {
            // Non-blocking
            \Illuminate\Support\Facades\Log::error("Gagal mengirim WhatsApp pendaftaran manual: " . $e->getMessage());
        }

        return redirect()->back()->with('success', "Peserta '{$validated['full_name']}' berhasil didaftarkan secara manual pada cabang {$competition->name}".($status === 'verified' ? ' dan langsung berstatus Lunas/Terverifikasi.' : '.'));
    }

    public function drawIndex()
    {
        $user = Auth::user();

        $competitions = Competition::with(['category', 'registrations' => function ($q) {
            $q->with('members');
        }])
            ->whereIn('id', self::getManagedCompetitionIds($user))
            ->get()
            ->map(function ($comp) {
                $verified = $comp->registrations->where('status', 'verified');
                $drawnCount = $verified->whereNotNull('draw_number')->count();
                $undrawnCount = $verified->whereNull('draw_number')->count();
                $totalVerified = $verified->count();

                return [
                    'id' => $comp->id,
                    'code' => $comp->code,
                    'name' => $comp->name,
                    'slug' => $comp->slug,
                    'category' => $comp->category->name ?? '-',
                    'total_registrations' => $comp->registrations->count(),
                    'total_verified' => $totalVerified,
                    'drawn_count' => $drawnCount,
                    'undrawn_count' => $undrawnCount,
                    'is_complete' => ($totalVerified > 0 && $undrawnCount === 0),
                ];
            });

        $totalDrawn = $competitions->sum('drawn_count');
        $totalUndrawn = $competitions->sum('undrawn_count');
        $totalVerifiedAll = $competitions->sum('total_verified');

        return view('pic.draw-index', compact('competitions', 'totalDrawn', 'totalUndrawn', 'totalVerifiedAll'));
    }

    public function hackerDraw($competition_id)
    {
        $user = Auth::user();
        $competition = Competition::with(['category', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with('members');
        }])->findOrFail($competition_id);

        $this->authorizeCompetitionManagement($user, $competition->id);

        $verifiedList = $competition->registrations->map(function ($reg) {
            $firstMember = $reg->members->first();
            $pureName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #'.$reg->id);

            return [
                'id' => $reg->id,
                'name' => $pureName,
                'institution' => $reg->institution_name,
                'participant_number' => $reg->participant_number,
                'registration_code' => $reg->registration_code,
                'gender' => $reg->primary_gender,
                'draw_number' => $reg->draw_number,
                'is_drawn' => ! is_null($reg->draw_number),
            ];
        });

        $undrawnList = $verifiedList->where('is_drawn', false)->values();
        $drawnList = $verifiedList->where('is_drawn', true)->sortBy('draw_number')->values();

        return view('pic.hacker-draw', compact('competition', 'verifiedList', 'undrawnList', 'drawnList'));
    }

    public function spinWheel($competition_id)
    {
        $user = Auth::user();
        $competition = Competition::with(['category', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with('members');
        }])->findOrFail($competition_id);

        $this->authorizeCompetitionManagement($user, $competition->id);

        $verifiedList = $competition->registrations->map(function ($reg) {
            $firstMember = $reg->members->first();
            $pureName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #'.$reg->id);

            return [
                'id' => $reg->id,
                'name' => $pureName,
                'institution' => $reg->institution_name,
                'participant_number' => $reg->participant_number,
                'draw_number' => $reg->draw_number,
                'is_drawn' => ! is_null($reg->draw_number),
            ];
        });

        $undrawnList = $verifiedList->where('is_drawn', false)->values();
        $drawnList = $verifiedList->where('is_drawn', true)->sortBy('draw_number')->values();

        return view('pic.spin-wheel', compact('competition', 'verifiedList', 'undrawnList', 'drawnList'));
    }

    public function storeDrawResult(Request $request, $competition_id)
    {
        $competition = Competition::findOrFail($competition_id);
        $user = Auth::user();
        $this->authorizeCompetitionManagement($user, $competition->id);

        $validated = $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'draw_number' => ['required', 'integer', 'min:1'],
        ]);

        $registration = Registration::where('id', $validated['registration_id'])
            ->where('competition_id', $competition->id)
            ->firstOrFail();

        $registration->draw_number = $validated['draw_number'];
        $registration->save();

        DrawAllocation::updateOrCreate(
            [
                'competition_id' => $competition->id,
                'registration_id' => $registration->id,
            ],
            [
                'draw_number' => $validated['draw_number'],
                'spun_at' => now(),
                'spun_by' => $user->id,
            ]
        );

        // Trigger Auto WhatsApp Notification: Hasil Undian Spin Wheel / Hacker Draw
        try {
            $registration->loadMissing(['members', 'user', 'competition']);
            $memberNisns = $registration->members->pluck('nisn')->filter()->unique();
            $nisn = $memberNisns->isNotEmpty() ? $memberNisns->implode(' / ') : ($registration->user?->nisn ?? '-');
            $targetPhones = $registration->recipient_phones;

            WablasNotificationService::sendAutoNotification('draw_result_picked', [
                'phone' => $targetPhones,
                'nama_peserta' => $registration->pure_name,
                'nisn' => $nisn,
                'nama_sekolah' => $registration->institution_name,
                'cabang_lomba' => $competition->name,
                'no_peserta' => $registration->participant_number ?: $registration->registration_code,
                'kode_pendaftaran' => $registration->registration_code,
                'nomor_undian' => $validated['draw_number'],
                'draw_number' => $validated['draw_number'],
                'link_scoreboard' => url('/'),
                'link_login' => route('login'),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return response()->json([
            'success' => true,
            'message' => 'Nomor undian '.$validated['draw_number'].' berhasil disimpan untuk '.$registration->display_name,
            'registration' => $registration,
        ]);
    }

    public function resetDraws(Request $request, $competition_id)
    {
        $competition = Competition::findOrFail($competition_id);

        Registration::where('competition_id', $competition->id)->update(['draw_number' => null]);
        DrawAllocation::where('competition_id', $competition->id)->delete();

        return back()->with('success', 'Semua nomor undian pada cabang '.$competition->name.' telah di-reset.');
    }
}
