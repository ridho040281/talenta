<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\DrawAllocation;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\User;
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
            \App\Models\AppSetting::get('blt_pic_tunggal_pa'),
            \App\Models\AppSetting::get('blt_pic_tunggal_pi'),
            \App\Models\AppSetting::get('blt_pic_ganda_pa'),
            \App\Models\AppSetting::get('blt_pic_ganda_pi'),
        ]);
        if (in_array($user->id, $bltPics) || in_array((string)$user->id, $bltPics)) {
            $extraCodes[] = 'BLT';
        }

        $tmjPics = array_filter([
            \App\Models\AppSetting::get('tmj_pic_tunggal_pa'),
            \App\Models\AppSetting::get('tmj_pic_tunggal_pi'),
        ]);
        if (in_array($user->id, $tmjPics) || in_array((string)$user->id, $tmjPics)) {
            $extraCodes[] = 'TMJ';
        }

        $mtqPics = array_filter([
            \App\Models\AppSetting::get('mtq_pic_pa'),
            \App\Models\AppSetting::get('mtq_pic_pi'),
        ]);
        if (in_array($user->id, $mtqPics) || in_array((string)$user->id, $mtqPics)) {
            $extraCodes[] = 'MTQ';
        }

        $popPics = array_filter([
            \App\Models\AppSetting::get('pop_pic_pa'),
            \App\Models\AppSetting::get('pop_pic_pi'),
        ]);
        if (in_array($user->id, $popPics) || in_array((string)$user->id, $popPics)) {
            $extraCodes[] = 'POP';
        }

        return Competition::where(function ($q) use ($user, $extraCodes) {
            $q->where('pic_id', $user->id);
            if (!empty($extraCodes)) {
                $q->orWhereIn('code', $extraCodes);
            }
        })->pluck('id')->toArray();
    }

    protected function authorizeCompetitionManagement($user, $competitionId): void
    {
        if (!in_array($competitionId, self::getManagedCompetitionIds($user))) {
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

        $categories = \App\Models\Category::all();

        return view('pic.dashboard', compact('user', 'competitions', 'stats', 'allRegistrations', 'categories'));
    }

    public function printParticipantsPdf(Request $request)
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

        // Group into printable separate pages
        $pages = [];
        $competitions = $registrations->groupBy('competition_id');

        foreach ($competitions as $compId => $compRegs) {
            $comp = $compRegs->first()->competition;
            $isBuluTangkis = ($comp->code === 'BLT' || stripos($comp->name, 'bulu tangkis') !== false || stripos($comp->name, 'badminton') !== false);

            if ($isBuluTangkis) {
                // 1. Tunggal Categories (Kat A, Kat B, Kat C)
                $bltCategories = [
                    'kat_a' => 'Kategori A (Kelas 1–2)',
                    'kat_b' => 'Kategori B (Kelas 3–4)',
                    'kat_c' => 'Kategori C (Kelas 5–6)',
                ];

                foreach ($bltCategories as $catKey => $catLabel) {
                    $catRegs = $compRegs->filter(function($r) use ($catKey) {
                        $targetStr = strtolower(($r->target_class ?? '') . ' ' . ($r->sub_category ?? '') . ' ' . ($r->match_type ?? ''));
                        if (stripos($targetStr, 'ganda') !== false) return false;
                        if ($catKey === 'kat_a') return (stripos($targetStr, 'kategori a') !== false || stripos($targetStr, 'kat a') !== false || stripos($targetStr, 'kat_a') !== false || stripos($targetStr, 'kelas 1') !== false || stripos($targetStr, 'kelas 2') !== false || stripos($targetStr, '-a-') !== false);
                        if ($catKey === 'kat_b') return (stripos($targetStr, 'kategori b') !== false || stripos($targetStr, 'kat b') !== false || stripos($targetStr, 'kat_b') !== false || stripos($targetStr, 'kelas 3') !== false || stripos($targetStr, 'kelas 4') !== false || stripos($targetStr, '-b-') !== false);
                        if ($catKey === 'kat_c') return (stripos($targetStr, 'kategori c') !== false || stripos($targetStr, 'kat c') !== false || stripos($targetStr, 'kat_c') !== false || stripos($targetStr, 'kelas 5') !== false || stripos($targetStr, 'kelas 6') !== false || stripos($targetStr, '-c-') !== false);
                        return false;
                    });

                    if ($catRegs->isNotEmpty()) {
                        $paRegs = $catRegs->filter(fn($r) => $r->primary_gender === 'L')->sortBy(fn($r) => $r->draw_number ?: 9999)->values();
                        $piRegs = $catRegs->filter(fn($r) => $r->primary_gender === 'P')->sortBy(fn($r) => $r->draw_number ?: 9999)->values();

                        if ($paRegs->isNotEmpty()) {
                            $pages[] = [
                                'competition_name' => $comp->name,
                                'sub_group_title' => '👦 KELOMPOK PUTRA (PA)',
                                'sector_title' => $catLabel . ' - TUNGGAL PUTRA',
                                'gender_badge_class' => 'bg-blue-100 text-blue-900',
                                'registrations' => $paRegs,
                            ];
                        }
                        if ($piRegs->isNotEmpty()) {
                            $pages[] = [
                                'competition_name' => $comp->name,
                                'sub_group_title' => '👧 KELOMPOK PUTRI (PI)',
                                'sector_title' => $catLabel . ' - TUNGGAL PUTRI',
                                'gender_badge_class' => 'bg-rose-100 text-rose-900',
                                'registrations' => $piRegs,
                            ];
                        }
                    }
                }

                // 2. Ganda Categories (Direct Ganda Putra & Ganda Putri)
                $gandaAllRegs = $compRegs->filter(function($r) {
                    $targetStr = strtolower(($r->target_class ?? '') . ' ' . ($r->sub_category ?? '') . ' ' . ($r->match_type ?? ''));
                    return (stripos($targetStr, 'ganda') !== false || $r->members->count() > 1);
                });

                if ($gandaAllRegs->isNotEmpty()) {
                    $gandaPa = $gandaAllRegs->filter(fn($r) => $r->primary_gender === 'L' || stripos($r->match_type, 'Putra') !== false || stripos($r->match_type, 'PA') !== false)->sortBy(fn($r) => $r->draw_number ?: 9999)->values();
                    $gandaPi = $gandaAllRegs->filter(fn($r) => $r->primary_gender === 'P' || stripos($r->match_type, 'Putri') !== false || stripos($r->match_type, 'PI') !== false)->sortBy(fn($r) => $r->draw_number ?: 9999)->values();

                    if ($gandaPa->isNotEmpty()) {
                        $pages[] = [
                            'competition_name' => $comp->name,
                            'sub_group_title' => '👥 KELOMPOK GANDA PUTRA (PA)',
                            'sector_title' => 'GANDA PUTRA (PA) - SEMUA KELAS',
                            'gender_badge_class' => 'bg-blue-100 text-blue-900',
                            'registrations' => $gandaPa,
                        ];
                    }
                    if ($gandaPi->isNotEmpty()) {
                        $pages[] = [
                            'competition_name' => $comp->name,
                            'sub_group_title' => '👥 KELOMPOK GANDA PUTRI (PI)',
                            'sector_title' => 'GANDA PUTRI (PI) - SEMUA KELAS',
                            'gender_badge_class' => 'bg-rose-100 text-rose-900',
                            'registrations' => $gandaPi,
                        ];
                    }
                }
            } else {
                // General Competition: Separate Page 1 (PA) and Page 2 (PI)
                $paRegs = $compRegs->filter(fn($r) => $r->primary_gender === 'L')->sortBy(fn($r) => $r->draw_number ?: 9999)->values();
                $piRegs = $compRegs->filter(fn($r) => $r->primary_gender === 'P')->sortBy(fn($r) => $r->draw_number ?: 9999)->values();
                $otherRegs = $compRegs->filter(fn($r) => !in_array($r->primary_gender, ['L', 'P']))->sortBy(fn($r) => $r->draw_number ?: 9999)->values();

                if ($paRegs->isNotEmpty() || ($piRegs->isEmpty() && $otherRegs->isEmpty())) {
                    $pages[] = [
                        'competition_name' => $comp->name,
                        'sub_group_title' => '👦 KELOMPOK PUTRA (PA)',
                        'sector_title' => $comp->category->name ?? 'Tingkat SD/MI',
                        'gender_badge_class' => 'bg-blue-100 text-blue-900',
                        'registrations' => $paRegs,
                    ];
                }
                if ($piRegs->isNotEmpty()) {
                    $pages[] = [
                        'competition_name' => $comp->name,
                        'sub_group_title' => '👧 KELOMPOK PUTRI (PI)',
                        'sector_title' => $comp->category->name ?? 'Tingkat SD/MI',
                        'gender_badge_class' => 'bg-rose-100 text-rose-900',
                        'registrations' => $piRegs,
                    ];
                }
                if ($otherRegs->isNotEmpty()) {
                    $pages[] = [
                        'competition_name' => $comp->name,
                        'sub_group_title' => '👥 KELOMPOK BEREGU / CAMPURAN',
                        'sector_title' => $comp->category->name ?? 'Tingkat SD/MI',
                        'gender_badge_class' => 'bg-purple-100 text-purple-900',
                        'registrations' => $otherRegs,
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
            if ($gA !== $gB) return $gA <=> $gB;
            return strcmp($a->participant_number ?? '', $b->participant_number ?? '');
        });

        $filename = 'DATA_PESERTA_TALENTA_2026_' . date('Ymd_His') . '.xls';

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

                echo '<tr class="' . $genderClass . '">
                    <td class="center">' . $no++ . '</td>
                    <td class="center">' . htmlspecialchars($reg->registration_code) . '</td>
                    <td class="center bold">' . htmlspecialchars($reg->participant_number ?: '-') . '</td>
                    <td class="center bold">' . htmlspecialchars($reg->draw_number ? '#' . $reg->draw_number : '-') . '</td>
                    <td class="bold">' . htmlspecialchars($reg->display_name) . '</td>
                    <td class="center">' . htmlspecialchars($firstMember?->nisn ?: '-') . '</td>
                    <td class="center bold">' . htmlspecialchars($genderLabel) . '</td>
                    <td>' . htmlspecialchars($reg->competition->name ?? '-') . '</td>
                    <td>' . htmlspecialchars(($reg->target_class ?: '') . ' ' . ($reg->sub_category ?: '')) . '</td>
                    <td>' . htmlspecialchars($reg->institution_name) . '</td>
                    <td>' . htmlspecialchars($reg->official_name ?: '-') . '</td>
                    <td class="center">' . htmlspecialchars($reg->official_phone ?: '-') . '</td>
                    <td class="center bold">' . ucfirst($reg->status) . '</td>
                </tr>';
            }

            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
            $count = Registration::where('competition_id', $registration->competition_id)
                ->whereNotNull('participant_number')
                ->count() + 1;
            $registration->participant_number = $registration->competition->code . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
        }

        $registration->save();

        \App\Models\ActivityLog::record(
            $validated['status'] === 'verified' ? 'VERIFY_SUCCESS' : 'VERIFY_REJECT',
            ($validated['status'] === 'verified' ? 'Memverifikasi sah' : 'Menolak') . " peserta '{$registration->display_name}' ({$registration->registration_code}) pada cabang {$registration->competition->name}" . ($validated['verification_notes'] ? ". Catatan: {$validated['verification_notes']}" : ''),
            $user,
            $validated['status'] === 'verified' ? 'success' : 'warning'
        );

        // Auto-sync invoice status if part of collective registration
        if ($registration->invoice_id && $registration->invoice) {
            $invoice = $registration->invoice;
            $allRegs = $invoice->registrations;
            
            if ($allRegs->every(fn($r) => $r->status === 'verified')) {
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

        // Trigger Auto WhatsApp Notification: Pendaftaran Terverifikasi Sah
        if ($validated['status'] === 'verified') {
            try {
                $registration->loadMissing(['members', 'user', 'competition']);
                $firstMember = $registration->members->first();
                $targetPhone = $registration->official_phone ?: ($registration->user?->phone ?: $firstMember?->phone);

                \App\Services\WablasNotificationService::sendAutoNotification('registration_verified', [
                    'phone' => $targetPhone,
                    'nama_peserta' => $registration->display_name,
                    'nisn' => $firstMember?->nisn ?? ($registration->user?->nisn ?? '-'),
                    'nama_sekolah' => $registration->institution_name,
                    'cabang_lomba' => $registration->competition->name,
                    'no_peserta' => $registration->participant_number ?: $registration->registration_code,
                    'kode_pendaftaran' => $registration->registration_code,
                    'link_scoreboard' => url('/'),
                    'link_login' => route('login'),
                ]);
            } catch (\Throwable $e) {
                // Non-blocking
            }
        }

        return back()->with('success', 'Status pendaftaran ' . $registration->registration_code . ' berhasil diubah menjadi: ' . ucfirst($validated['status']));
    }

    public function updateParticipantData(Request $request, $registration_id)
    {
        $registration = Registration::with(['competition', 'members'])->findOrFail($registration_id);
        $user = Auth::user();

        if ($user->role === 'pic_lomba' && $registration->competition->pic_id !== $user->id) {
            abort(403);
        }

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

        $registration->institution_name = $validated['institution_name'];
        $registration->official_name = $validated['official_name'];
        $registration->official_phone = $validated['official_phone'];
        $registration->team_name = $validated['team_name'] ?? null;
        $registration->target_class = $validated['target_class'] ?? null;
        if (!empty($validated['match_type'])) {
            $registration->match_type = $validated['match_type'];
        }
        $registration->participant_number = $validated['participant_number'] ?? null;
        $registration->draw_number = !empty($validated['draw_number']) ? $validated['draw_number'] : null;

        if ($registration->competition && $registration->competition->code === 'BLT') {
            if ($registration->match_type && stripos($registration->match_type, 'ganda') !== false) {
                $registration->target_class = 'Ganda (Semua Kelas)';
                $registration->sub_category = $registration->match_type;
            } elseif (!empty($registration->target_class) && !empty($registration->match_type)) {
                $registration->sub_category = $registration->target_class . ' - ' . $registration->match_type;
            }
        }

        if ($request->hasFile('document_file')) {
            $registration->document_file = $request->file('document_file')->store('documents', 'public');
        }
        if ($request->hasFile('payment_proof')) {
            $registration->payment_proof = $request->file('payment_proof')->store('payments', 'public');
        }

        $registration->save();

        // Update member records
        foreach ($validated['members'] as $mData) {
            if (!empty($mData['id'])) {
                $member = \App\Models\RegistrationMember::where('registration_id', $registration->id)->find($mData['id']);
                if ($member) {
                    $member->update([
                        'full_name' => $mData['full_name'],
                        'school_name' => $mData['school_name'] ?? null,
                        'nisn' => $mData['nisn'] ?? null,
                        'gender' => $mData['gender'],
                        'birth_place' => $mData['birth_place'] ?? null,
                        'birth_date' => $mData['birth_date'] ?? null,
                        'phone' => $mData['phone'] ?? null,
                    ]);
                }
            }
        }

        return back()->with('success', 'Data pendaftaran ' . $registration->display_name . ' (' . $registration->registration_code . ') berhasil diperbarui oleh Admin.');
    }

    public function unverifyParticipant($registration_id)
    {
        $registration = Registration::with(['competition', 'invoice.registrations'])->findOrFail($registration_id);
        $user = Auth::user();

        if ($user->role === 'pic_lomba' && $registration->competition->pic_id !== $user->id) {
            abort(403);
        }

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

        \App\Models\ActivityLog::record(
            'UNVERIFY',
            "Membatalkan verifikasi pendaftaran '{$registration->display_name}' ({$registration->registration_code}) kembali ke Pending",
            $user,
            'warning'
        );

        return back()->with('success', 'Verifikasi pendaftaran ' . $registration->registration_code . ' telah dibatalkan. Status otomatis kembali menjadi Menunggu dan peserta dapat mengedit data di akunnya.');
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

        \App\Models\ActivityLog::record(
            'DELETE_PARTICIPANT',
            "Menghapus data pendaftaran peserta '{$name}' ({$code}) secara permanen",
            $user,
            'danger'
        );

        return back()->with('success', 'Data pendaftaran ' . $name . ' (' . $code . ') berhasil dihapus secara permanen.');
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
        ]);

        $competition = Competition::findOrFail($validated['competition_id']);
        $this->authorizeCompetitionManagement($user, $competition->id);

        // Check quota if ignore_quota is not checked
        if (!$request->boolean('ignore_quota')) {
            if ($competition->quota > 0 && !in_array($competition->code, ['BLT', 'TMJ', 'MTQ', 'POP'])) {
                $currentTotal = Registration::where('competition_id', $competition->id)
                    ->whereIn('status', ['pending', 'verified'])
                    ->count();
                if ($currentTotal >= $competition->quota) {
                    return back()->with('error', "Kuota pendaftaran untuk {$competition->name} sudah penuh ({$competition->quota} peserta). Centang opsi 'Abaikan Kuota' jika ini merupakan dispensasi khusus.")->withInput();
                }
            }
        }

        // Find or create User for the participant so they can login to portal if needed
        $nisnClean = !empty($validated['nisn']) ? trim($validated['nisn']) : null;
        $participantUser = null;
        if ($nisnClean) {
            $participantUser = User::where('nisn', $nisnClean)->first();
        }
        if (!$participantUser) {
            $randomSuffix = rand(100, 999);
            $autoNisn = $nisnClean ?: ('MNL' . date('ymd') . $randomSuffix);
            $email = $autoNisn . '@peserta.talenta';
            if (User::where('email', $email)->exists()) {
                $email = $autoNisn . '_' . rand(10, 99) . '@peserta.talenta';
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
        }

        // Prevent duplicate NISN in same competition
        if ($nisnClean) {
            $existingMember = RegistrationMember::where('nisn', $nisnClean)
                ->whereHas('registration', function($q) use ($competition) {
                    $q->where('competition_id', $competition->id)
                      ->whereIn('status', ['pending', 'verified']);
                })
                ->first();

            if ($existingMember) {
                return back()->with('error', "Peserta dengan NISN '{$nisnClean}' ({$existingMember->full_name}) sudah terdaftar pada cabang {$competition->name}.")->withInput();
            }
        }

        // Determine sub_category
        $subCategory = null;
        if (!empty($validated['target_class']) && !empty($validated['match_type'])) {
            $subCategory = $validated['target_class'] . ' - ' . $validated['match_type'];
        } elseif (!empty($validated['match_type'])) {
            $subCategory = $validated['match_type'];
        }

        $regCode = 'REG-' . date('Y') . '-' . $competition->code . '-' . strtoupper(Str::random(5));

        $teamName = null;
        if (!empty($validated['member2_name'])) {
            $teamName = $validated['full_name'] . ' / ' . $validated['member2_name'];
        }

        $status = $validated['status'];
        $notes = $validated['verification_notes'] ?: ($status === 'verified' ? 'Didaftarkan manual oleh ' . $user->name . ' (Lunas Tunai)' : 'Pendaftaran manual (Menunggu Verifikasi)');

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $participantUser->id,
            'registration_code' => $regCode,
            'team_name' => $teamName,
            'sub_category' => $subCategory,
            'target_class' => $validated['target_class'] ?? null,
            'match_type' => $validated['match_type'] ?? null,
            'institution_name' => $validated['institution_name'],
            'official_name' => $user->name,
            'official_phone' => $validated['phone'] ?? $user->phone,
            'status' => $status,
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
            'gender' => $validated['gender'],
            'phone' => $validated['phone'] ?? null,
            'role_in_team' => !empty($validated['member2_name']) ? 'Pemain 1' : 'Peserta Utama',
        ]);

        // Create Member 2 if Ganda
        if (!empty($validated['member2_name'])) {
            RegistrationMember::create([
                'registration_id' => $registration->id,
                'full_name' => $validated['member2_name'],
                'school_name' => $validated['member2_school'] ?: $validated['institution_name'],
                'nisn' => !empty($validated['member2_nisn']) ? trim($validated['member2_nisn']) : null,
                'gender' => $validated['gender'],
                'role_in_team' => 'Pemain 2',
            ]);
        }

        \App\Models\ActivityLog::record(
            'MANUAL_REGISTRATION',
            "Mendaftarkan peserta baru '{$validated['full_name']}' secara manual pada cabang {$competition->name}" . ($status === 'verified' ? ' (Langsung Terverifikasi/Lunas Tunai)' : ' (Status: Pending)'),
            $user,
            'success'
        );

        return redirect()->back()->with('success', "Peserta '{$validated['full_name']}' berhasil didaftarkan secara manual pada cabang {$competition->name}" . ($status === 'verified' ? " dan langsung berstatus Lunas/Terverifikasi." : "."));
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
            $pureName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #' . $reg->id);

            return [
                'id' => $reg->id,
                'name' => $pureName,
                'institution' => $reg->institution_name,
                'participant_number' => $reg->participant_number,
                'registration_code' => $reg->registration_code,
                'gender' => $reg->primary_gender,
                'draw_number' => $reg->draw_number,
                'is_drawn' => !is_null($reg->draw_number),
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
            $pureName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #' . $reg->id);

            return [
                'id' => $reg->id,
                'name' => $pureName,
                'institution' => $reg->institution_name,
                'participant_number' => $reg->participant_number,
                'draw_number' => $reg->draw_number,
                'is_drawn' => !is_null($reg->draw_number),
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
            $firstMember = $registration->members->first();
            $targetPhone = $registration->official_phone ?: ($registration->user?->phone ?: $firstMember?->phone);

            \App\Services\WablasNotificationService::sendAutoNotification('draw_result_picked', [
                'phone' => $targetPhone,
                'nama_peserta' => $registration->display_name,
                'nisn' => $firstMember?->nisn ?? ($registration->user?->nisn ?? '-'),
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
            'message' => 'Nomor undian ' . $validated['draw_number'] . ' berhasil disimpan untuk ' . $registration->display_name,
            'registration' => $registration,
        ]);
    }

    public function resetDraws(Request $request, $competition_id)
    {
        $competition = Competition::findOrFail($competition_id);
        
        Registration::where('competition_id', $competition->id)->update(['draw_number' => null]);
        DrawAllocation::where('competition_id', $competition->id)->delete();

        return back()->with('success', 'Semua nomor undian pada cabang ' . $competition->name . ' telah di-reset.');
    }
}
