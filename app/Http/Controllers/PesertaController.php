<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Services\ImageOptimizerService;
use App\Services\WablasNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PesertaController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $registrations = Registration::with(['competition.category', 'members', 'scores', 'invoice'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $invoices = Invoice::with(['registrations.competition'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $openCompetitions = Competition::with('category')
            ->where('status', 'buka')
            ->orderBy('order', 'asc')
            ->get();

        $categories = Category::orderBy('order', 'asc')->get();

        return view('peserta.dashboard', compact('user', 'registrations', 'invoices', 'openCompetitions', 'categories'));
    }

    public function myRegistrations()
    {
        $user = Auth::user();
        $registrations = Registration::with(['competition.category', 'members', 'scores', 'invoice'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $invoices = Invoice::with(['registrations.competition'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('peserta.my-registrations', compact('user', 'registrations', 'invoices'));
    }

    public function showRegisterCompetition($slug)
    {
        $competition = Competition::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        $regInfo = AppSetting::getRegistrationStatusInfo();
        if (!$regInfo['is_open'] && !$user->isTester()) {
            $msg = $regInfo['status_code'] === 'not_started'
                ? 'Pendaftaran TALENTA 2026 belum dibuka. Pendaftaran dibuka mulai ' . ($regInfo['start_date_formatted'] ?: '-') . ' WIB.'
                : ($regInfo['closed_message'] ?: 'Pendaftaran perlombaan saat ini telah resmi ditutup.');
            return redirect()->route('peserta.dashboard')->with('error', $msg);
        }

        if ($competition->status === 'tutup') {
            return redirect()->route('peserta.dashboard')
                ->with('error', 'Pendaftaran untuk cabang lomba '.$competition->name.' telah ditutup.');
        }

        // Check if user already registered for this competition
        $existing = Registration::where('user_id', $user->id)
            ->where('competition_id', $competition->id)
            ->first();

        if ($existing) {
            return redirect()->route('peserta.registration.detail', $existing->id)
                ->with('info', 'Anda sudah terdaftar pada cabang lomba '.$competition->name.' (Kode Reg: '.$existing->registration_code.'). Anda bebas mendaftar pada cabang lomba yang berbeda di Dashboard.');
        }

        $bankInfo = [
            'bank_name' => AppSetting::get('bank_name', 'Bank Syariah Indonesia (BSI)'),
            'bank_account_number' => AppSetting::get('bank_account_number', '7199242042'),
            'bank_account_holder' => AppSetting::get('bank_account_holder', 'WIJIATIN'),
        ];

        return view('peserta.register-competition', compact('competition', 'user', 'existing', 'bankInfo'));
    }

    public function storeRegistration(Request $request, $slug)
    {
        $competition = Competition::where('slug', $slug)->firstOrFail();
        $user = Auth::user();
        $isBuluTangkis = ($competition->code === 'BLT');

        $regInfo = AppSetting::getRegistrationStatusInfo();
        if (!$regInfo['is_open'] && !$user->isTester()) {
            $msg = $regInfo['status_code'] === 'not_started'
                ? 'Pendaftaran TALENTA 2026 belum dibuka. Pendaftaran dibuka mulai ' . ($regInfo['start_date_formatted'] ?: '-') . ' WIB.'
                : ($regInfo['closed_message'] ?: 'Pendaftaran perlombaan saat ini telah resmi ditutup.');
            return back()->with('error', $msg);
        }

        if ($competition->status === 'tutup') {
            return back()->with('error', 'Pendaftaran untuk cabang lomba '.$competition->name.' telah ditutup.');
        }

        // Prevent duplicate registration in the same competition for this user account
        $existingUserReg = Registration::where('user_id', $user->id)
            ->where('competition_id', $competition->id)
            ->first();

        if ($existingUserReg) {
            return redirect()->route('peserta.registration.detail', $existingUserReg->id)
                ->with('error', 'Anda sudah terdaftar pada cabang lomba '.$competition->name.'. Silakan pilih cabang lomba lain jika ingin mengikuti lebih dari satu lomba.');
        }

        // Enforce quota limit only if quota is explicitly greater than 0 (0 = unlimited)
        if ($competition->quota > 0 && ! in_array($competition->code, ['BLT', 'TMJ'])) {
            $currentTotal = Registration::where('competition_id', $competition->id)
                ->whereIn('status', ['pending', 'verified'])
                ->count();
            if ($currentTotal >= $competition->quota) {
                return back()->with('error', 'Mohon maaf, kuota pendaftaran untuk cabang lomba '.$competition->name.' telah penuh ('.$competition->quota.' peserta).');
            }
        }

        $isBuluTangkis = ($competition->code === 'BLT');
        $isTenisMeja = ($competition->code === 'TMJ');
        $isPopSinger = ($competition->code === 'POP' || \Illuminate\Support\Str::contains(strtolower($competition->slug), 'pop') || \Illuminate\Support\Str::contains(strtolower($competition->name), 'pop'));
        $isPramuka = ($competition->code === 'PRM' || \Illuminate\Support\Str::contains(strtolower($competition->slug), 'pramuka') || \Illuminate\Support\Str::contains(strtolower($competition->name), 'pramuka'));
        $isGandaBlt = $isBuluTangkis && (stripos($request->input('match_type', ''), 'Ganda') !== false);

        // Enforce tier quotas for Tenis Meja
        if ($isTenisMeja) {
            $tierQuotas = $competition->tier_quotas;
            $matchType = $request->input('match_type');
            $targetClass = $request->input('target_class');
            $isPa = stripos($matchType, 'Putra') !== false || stripos($matchType, 'PA') !== false;
            $isKatA = stripos($targetClass, 'Kategori A') !== false || stripos($targetClass, '1 - 3') !== false;
            $quotaKey = ($isKatA ? 'A' : 'B').'_tunggal_'.($isPa ? 'pa' : 'pi');
            $maxQuota = (int) ($tierQuotas[$quotaKey] ?? 0);
            if ($maxQuota > 0) {
                $currentTierTotal = Registration::where('competition_id', $competition->id)
                    ->whereIn('status', ['pending', 'verified'])
                    ->where('match_type', $matchType)
                    ->where('target_class', $targetClass)
                    ->count();
                if ($currentTierTotal >= $maxQuota) {
                    return back()->with('error', "Mohon maaf, kuota pendaftaran Tenis Meja untuk {$targetClass} - {$matchType} telah penuh ({$maxQuota} peserta).");
                }
            }
        }

        // Enforce tier quotas for Bulu Tangkis
        if ($isBuluTangkis) {
            $tierQuotas = $competition->tier_quotas;
            $matchType = $request->input('match_type');
            $targetClass = $request->input('target_class');
            $isGanda = stripos($matchType, 'Ganda') !== false;
            $isPa = stripos($matchType, 'Putra') !== false || stripos($matchType, 'PA') !== false;
            if ($isGanda) {
                $quotaKey = 'ganda_'.($isPa ? 'pa' : 'pi');
                $maxQuota = (int) ($tierQuotas[$quotaKey] ?? 0);
                if ($maxQuota > 0) {
                    $currentTierTotal = Registration::where('competition_id', $competition->id)
                        ->whereIn('status', ['pending', 'verified'])
                        ->where('match_type', $matchType)
                        ->count();
                    if ($currentTierTotal >= $maxQuota) {
                        return back()->with('error', "Mohon maaf, kuota pendaftaran Bulu Tangkis untuk {$matchType} telah penuh ({$maxQuota} regu).");
                    }
                }
            } else {
                $kat = 'A';
                if (stripos($targetClass, 'Kategori B') !== false) {
                    $kat = 'B';
                } elseif (stripos($targetClass, 'Kategori C') !== false) {
                    $kat = 'C';
                }
                $quotaKey = $kat.'_tunggal_'.($isPa ? 'pa' : 'pi');
                $maxQuota = (int) ($tierQuotas[$quotaKey] ?? 0);
                if ($maxQuota > 0) {
                    $currentTierTotal = Registration::where('competition_id', $competition->id)
                        ->whereIn('status', ['pending', 'verified'])
                        ->where('match_type', $matchType)
                        ->where('target_class', $targetClass)
                        ->count();
                    if ($currentTierTotal >= $maxQuota) {
                        return back()->with('error', "Mohon maaf, kuota pendaftaran Bulu Tangkis untuk {$targetClass} - {$matchType} telah penuh ({$maxQuota} peserta).");
                    }
                }
            }
        }

        $minMembers = 1;
        $maxMembers = 10;
        if ($isBuluTangkis) {
            $isGanda = str_contains($request->input('match_type', ''), 'Ganda');
            $minMembers = $isGanda ? 2 : 1;
            $maxMembers = $isGanda ? 2 : 1;
        } elseif ($isTenisMeja) {
            $minMembers = 1;
            $maxMembers = 1;
        } else {
            $minMembers = max(1, (int) ($competition->min_members ?? 1));
            $maxMembers = max($minMembers, (int) ($competition->max_members ?? 10));
        }

        $validated = $request->validate([
            'target_class' => [($isBuluTangkis && ! $isGandaBlt) || $isTenisMeja ? 'required' : 'nullable', 'string', 'max:50'],
            'match_type' => [$isBuluTangkis || $isTenisMeja ? 'required' : 'nullable', 'string', 'max:50'],
            'team_name' => [($competition->isCollective() && ! $isBuluTangkis && ! $isTenisMeja) ? 'required' : 'nullable', 'string', 'max:255'],
            'institution_name' => [$isGandaBlt ? 'nullable' : 'required', 'string', 'max:255'],
            'official_name' => ['nullable', 'string', 'max:255'],
            'official_phone' => ['nullable', 'string', 'max:20'],
            'official_gender' => [$isPramuka ? 'required' : 'nullable', 'in:L,P'],
            'official_photo' => [$isPramuka ? 'required' : 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,bmp', 'max:25600'],
            'members' => ['required', 'array', "min:{$minMembers}", "max:{$maxMembers}"],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.school_name' => ['nullable', 'string', 'max:255'],
            'members.*.nisn' => ['nullable', 'string', 'max:20'],
            'members.*.gender' => ['required', 'in:L,P'],
            'members.*.birth_place' => ['nullable', 'string', 'max:100'],
            'members.*.birth_date' => ['nullable', 'date'],
            'members.*.phone' => ['nullable', 'string', 'max:20'],
            'members.*.role_in_team' => ['nullable', 'string', 'max:100'],
            'members.*.photo' => [$isPramuka ? 'required' : 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,bmp', 'max:25600'],
            'chosen_song' => [$isPopSinger ? 'required' : 'nullable', 'string', 'max:255'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,zip', 'max:5120'],
            'payment_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'official_gender.required' => 'Jenis kelamin pembina / pendamping wajib dipilih.',
            'official_photo.required' => 'Foto pembina / pendamping wajib diunggah untuk cabang lomba Pramuka.',
            'official_photo.max' => 'Ukuran berkas foto pembina / pendamping maksimal 25 MB.',
            'official_photo.mimes' => 'Format foto pembina / pendamping harus berupa gambar (JPG, JPEG, PNG, atau WEBP).',
            'members.*.photo.required' => 'Foto peserta wajib diunggah untuk cabang lomba Pramuka.',
            'members.*.photo.max' => 'Ukuran berkas foto peserta maksimal 25 MB.',
            'members.*.photo.mimes' => 'Format foto peserta harus berupa gambar (JPG, JPEG, PNG, atau WEBP).',
            'chosen_song.required' => 'Judul lagu pilihan wajib dipilih untuk cabang lomba Pop Singer.',
            'target_class.required' => 'Kategori kelas wajib dipilih.',
            'match_type.required' => 'Kategori sektor pertandingan (Tunggal PA/PI) wajib dipilih.',
            'payment_proof.required' => 'Bukti pendaftaran / slip pembayaran wajib diunggah dalam satu kali pengiriman.',
            'team_name.required' => 'Nama regu / tim wajib diisi.',
            'institution_name.required' => 'Nama sekolah/madrasah asal wajib diisi.',
            'members.required' => 'Data anggota peserta wajib diisi.',
            'members.min' => "Jumlah anggota minimal untuk {$competition->name} adalah {$minMembers} orang.",
            'members.max' => "Jumlah anggota maksimal untuk {$competition->name} adalah {$maxMembers} orang.",
            'members.*.full_name.required' => 'Nama lengkap peserta wajib diisi.',
            'members.*.gender.required' => 'Jenis kelamin peserta wajib dipilih.',
        ]);

        // Prevent duplicate registration for member with same NISN in the SAME competition / sector
        foreach ($validated['members'] as $memberData) {
            if (! empty($memberData['nisn'])) {
                $checkNisn = trim($memberData['nisn']);
                $existingMember = RegistrationMember::where('nisn', $checkNisn)
                    ->whereHas('registration', function ($q) use ($competition, $isBuluTangkis, $isGandaBlt) {
                        $q->where('competition_id', $competition->id)
                            ->whereIn('status', ['pending', 'verified']);

                        if ($isBuluTangkis) {
                            if ($isGandaBlt) {
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
                    })
                    ->first();

                if ($existingMember) {
                    $sectorText = $isBuluTangkis ? ('sektor '.($isGandaBlt ? 'Ganda' : 'Tunggal').' ') : '';

                    return back()->withErrors([
                        'members' => "Peserta dengan NISN '{$checkNisn}' ({$existingMember->full_name}) sudah terdaftar pada {$sectorText}cabang lomba {$competition->name}.",
                    ])->withInput();
                }
            }
        }

        // Generate Registration Code: REG-YEAR-CODE-RANDOM
        $regCode = 'REG-'.date('Y').'-'.$competition->code.'-'.strtoupper(Str::random(5));

        $docPath = null;
        if ($request->hasFile('document_file')) {
            $docPath = $request->file('document_file')->store('documents', 'public');
            AdminSettingsController::ensurePublicStorageSync($docPath);
        }

        $paymentPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentPath = $request->file('payment_proof')->store('payments', 'public');
            AdminSettingsController::ensurePublicStorageSync($paymentPath);
        }

        $targetClass = $validated['target_class'] ?? null;
        $subCategory = null;
        if ($isBuluTangkis) {
            if ($isGandaBlt) {
                $targetClass = 'Ganda (Semua Kelas)';
                $subCategory = $validated['match_type'] ?? 'Ganda';
            } elseif (! empty($validated['target_class']) && ! empty($validated['match_type'])) {
                $subCategory = $validated['target_class'].' - '.$validated['match_type'];
            }
        } elseif ($isTenisMeja) {
            if (! empty($validated['target_class']) && ! empty($validated['match_type'])) {
                $subCategory = $validated['target_class'].' - '.$validated['match_type'];
            }
        } elseif ($isPopSinger || $competition->code === 'MTQ') {
            $firstGender = $validated['members'][0]['gender'] ?? 'L';
            $subCategory = ($firstGender === 'P') ? 'Putri (PI)' : 'Putra (PA)';
            $validated['match_type'] = $subCategory;
        }

        $teamName = $validated['team_name'] ?? null;
        if ($isBuluTangkis && $isGandaBlt && empty($teamName)) {
            $memberNames = array_column($validated['members'], 'full_name');
            $teamName = implode(' / ', array_filter($memberNames));
        }

        $institutionName = $validated['institution_name'] ?? null;
        if ($isBuluTangkis && $isGandaBlt) {
            $memberSchools = array_unique(array_filter(array_column($validated['members'], 'school_name')));
            if (! empty($memberSchools)) {
                $institutionName = implode(' / ', $memberSchools);
            }
        }
        if (empty($institutionName)) {
            $institutionName = $validated['institution_name'] ?? $user->institution_name ?? 'Kontingen Mandiri';
        }

        // Upload & Auto-Naming + Auto-Kompresi (Maks ~200KB) Foto Official/Pendamping
        $officialPhotoPath = null;
        if ($request->hasFile('official_photo')) {
            $officialFile = $request->file('official_photo');
            $rawOfficialName = trim($validated['official_name'] ?? $user->name ?? 'Pendamping');
            $officialGender = $validated['official_gender'] ?? 'L';
            $rawPangkalan = trim($institutionName ?: ($user->institution_name ?: 'Pangkalan'));

            // Format: Nama Pendamping_Jeniskelamin (L/P)_Pangkalan.jpg
            // Contoh: Sulis_L_MIN 3 Malang.jpg
            $cleanOfficialName = preg_replace('/[\\\\\/:\*\?"<>|]/', '', $rawOfficialName);
            $cleanPangkalan = preg_replace('/[\\\\\/:\*\?"<>|]/', '', $rawPangkalan);
            $officialFileName = "{$cleanOfficialName}_{$officialGender}_{$cleanPangkalan}.jpg";

            $officialPhotoPath = ImageOptimizerService::optimizeAndStore(
                $officialFile,
                'photos/pramuka/officials',
                $officialFileName,
                1080,
                200
            );
        }

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'registration_code' => $regCode,
            'team_name' => $teamName,
            'sub_category' => $subCategory,
            'chosen_song' => $validated['chosen_song'] ?? $request->input('chosen_song'),
            'target_class' => $targetClass,
            'match_type' => $validated['match_type'] ?? null,
            'institution_name' => $institutionName,
            'official_name' => $validated['official_name'] ?? $user->name,
            'official_phone' => $validated['official_phone'] ?? $user->phone,
            'official_gender' => $validated['official_gender'] ?? null,
            'official_photo' => $officialPhotoPath,
            'status' => 'pending',
            'document_file' => $docPath,
            'payment_proof' => $paymentPath,
        ]);

        foreach ($validated['members'] as $index => $memberData) {
            $memberPhotoPath = null;
            if ($request->hasFile("members.{$index}.photo")) {
                $memberFile = $request->file("members.{$index}.photo");
                $rawNisn = ! empty($memberData['nisn']) ? preg_replace('/[^0-9]/', '', $memberData['nisn']) : 'NONISN';
                $rawMemberName = trim($memberData['full_name']);
                $cleanMemberName = preg_replace('/[\\\\\/:\*\?"<>|]/', '', $rawMemberName);

                // Format: NISN_Nama.jpg
                // Contoh: 3123412231_Joko Kelana.jpg
                $memberFileName = "{$rawNisn}_{$cleanMemberName}.jpg";

                $memberPhotoPath = ImageOptimizerService::optimizeAndStore(
                    $memberFile,
                    'photos/pramuka/members',
                    $memberFileName,
                    1080,
                    200
                );
            }

            RegistrationMember::create([
                'registration_id' => $registration->id,
                'full_name' => $memberData['full_name'],
                'school_name' => $memberData['school_name'] ?? $validated['institution_name'] ?? $user->institution_name ?? null,
                'nisn' => $memberData['nisn'] ?? null,
                'gender' => $memberData['gender'],
                'birth_place' => $memberData['birth_place'] ?? null,
                'birth_date' => $memberData['birth_date'] ?? null,
                'phone' => $memberData['phone'] ?? null,
                'photo' => $memberPhotoPath,
                'role_in_team' => $memberData['role_in_team'] ?? ($competition->isCollective() ? 'Anggota '.($index + 1) : ($isGandaBlt ? 'Pemain '.($index + 1) : 'Peserta Utama')),
            ]);
        }

        // Trigger Auto WhatsApp Notification: Pengiriman Pendaftaran Lomba (Multi-Notifikasi: Peserta, Official, PIC, dan Bendahara)
        try {
            $registration->loadMissing(['members', 'user', 'competition']);
            $firstMember = $registration->members->first();
            $targetPhones = $registration->recipient_phones;

            // 1. Ke Pendaftar & Official (Keduanya dikirim jika diisi)
            WablasNotificationService::sendAutoNotification('registration_submitted', [
                'phone' => $targetPhones,
                'nama_peserta' => $registration->pure_name,
                'nisn' => $firstMember?->nisn ?? ($user->nisn ?? '-'),
                'nama_sekolah' => $registration->institution_name,
                'cabang_lomba' => $competition->name,
                'kode_pendaftaran' => $registration->registration_code,
                'link_login' => route('peserta.registration.detail', $registration->id),
            ]);

            // 2. Ke PIC Cabang Lomba (Pendaftar Masuk & Siap Diverifikasi)
            WablasNotificationService::notifyPicNewRegistration($registration);

            // 3. Ke Bendahara Panitia (Pembayaran Masuk & Cek Mutasi Rekening)
            WablasNotificationService::notifyTreasurerNewPayment($registration, $registration->fee);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return redirect()->route('peserta.registration.detail', $registration->id)
            ->with('success', 'Pendaftaran berhasil dikirim! Kode Pendaftaran Anda: '.$regCode.'. Tim panitia akan memverifikasi berkas Anda.');
    }

    public function showRegistrationDetail($id)
    {
        $user = Auth::user();
        $registration = Registration::with(['competition.category', 'members', 'scores.details.criterion', 'verifier'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('peserta.registration-detail', compact('registration', 'user'));
    }

    public function updateRevision(Request $request, $id)
    {
        $user = Auth::user();
        $registration = Registration::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        if ($registration->status !== 'revision') {
            return back()->with('error', 'Pendaftaran tidak dalam status revisi.');
        }

        if ($request->hasFile('document_file')) {
            $docPath = $request->file('document_file')->store('documents', 'public');
            AdminSettingsController::ensurePublicStorageSync($docPath);
            $registration->document_file = $docPath;
        }

        if ($request->hasFile('payment_proof')) {
            $paymentPath = $request->file('payment_proof')->store('payments', 'public');
            AdminSettingsController::ensurePublicStorageSync($paymentPath);
            $registration->payment_proof = $paymentPath;
        }

        $registration->status = 'pending';
        $registration->verification_notes = 'Revisi berkas telah diunggah oleh peserta pada '.now()->format('d M Y H:i');
        $registration->save();

        return back()->with('success', 'Berkas revisi berhasil diunggah! Mohon menunggu verifikasi ulang.');
    }

    public function printIdCard($id)
    {
        $user = Auth::user();
        $registration = Registration::with(['competition.category', 'members'])
            ->where('id', $id)
            ->when($user->role === 'peserta', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        if ($registration->status !== 'verified') {
            return back()->with('error', 'Kartu Tanda Peserta hanya dapat dicetak setelah pendaftaran diverifikasi oleh panitia.');
        }

        return view('peserta.print-idcard', compact('registration'));
    }
}
