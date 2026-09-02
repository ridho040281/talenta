<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\RegistrationMember;
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

        $invoices = \App\Models\Invoice::with(['registrations.competition'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $openCompetitions = Competition::with('category')
            ->where('status', 'buka')
            ->get();

        return view('peserta.dashboard', compact('user', 'registrations', 'invoices', 'openCompetitions'));
    }

    public function myRegistrations()
    {
        $user = Auth::user();
        $registrations = Registration::with(['competition.category', 'members', 'scores', 'invoice'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $invoices = \App\Models\Invoice::with(['registrations.competition'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('peserta.my-registrations', compact('user', 'registrations', 'invoices'));
    }

    public function showRegisterCompetition($slug)
    {
        $competition = Competition::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        // Check if user already registered for this competition
        $existing = Registration::where('user_id', $user->id)
            ->where('competition_id', $competition->id)
            ->first();

        if ($existing) {
            return redirect()->route('peserta.registration.detail', $existing->id)
                ->with('info', 'Anda sudah terdaftar pada cabang lomba ' . $competition->name . ' (Kode Reg: ' . $existing->registration_code . '). Anda bebas mendaftar pada cabang lomba yang berbeda di Dashboard.');
        }

        return view('peserta.register-competition', compact('competition', 'user', 'existing'));
    }

    public function storeRegistration(Request $request, $slug)
    {
        $competition = Competition::where('slug', $slug)->firstOrFail();
        $user = Auth::user();
        $isBuluTangkis = ($competition->code === 'BLT');

        // Prevent duplicate registration in the same competition for this user account
        $existingUserReg = Registration::where('user_id', $user->id)
            ->where('competition_id', $competition->id)
            ->first();

        if ($existingUserReg) {
            return redirect()->route('peserta.registration.detail', $existingUserReg->id)
                ->with('error', 'Anda sudah terdaftar pada cabang lomba ' . $competition->name . '. Silakan pilih cabang lomba lain jika ingin mengikuti lebih dari satu lomba.');
        }

        if ($competition->status === 'tutup') {
            return back()->with('error', 'Pendaftaran untuk cabang lomba ' . $competition->name . ' telah ditutup.');
        }

        // Enforce quota limit only if quota is explicitly greater than 0 (0 = unlimited)
        if ($competition->quota > 0 && !in_array($competition->code, ['BLT', 'TMJ', 'MTQ', 'POP'])) {
            $currentTotal = Registration::where('competition_id', $competition->id)->count();
            if ($currentTotal >= $competition->quota) {
                return back()->with('error', 'Mohon maaf, kuota pendaftaran untuk cabang lomba ' . $competition->name . ' telah penuh (' . $competition->quota . ' peserta).');
            }
        }

        $isBuluTangkis = ($competition->code === 'BLT');
        $isTenisMeja = ($competition->code === 'TMJ');
        $isGandaBlt = $isBuluTangkis && (stripos($request->input('match_type', ''), 'Ganda') !== false);

        $validated = $request->validate([
            'target_class' => [($isBuluTangkis && !$isGandaBlt) || $isTenisMeja ? 'required' : 'nullable', 'string', 'max:50'],
            'match_type' => [$isBuluTangkis || $isTenisMeja ? 'required' : 'nullable', 'string', 'max:50'],
            'team_name' => [($competition->isCollective() && !$isBuluTangkis && !$isTenisMeja) ? 'required' : 'nullable', 'string', 'max:255'],
            'institution_name' => [$isGandaBlt ? 'nullable' : 'required', 'string', 'max:255'],
            'official_name' => ['nullable', 'string', 'max:255'],
            'official_phone' => ['nullable', 'string', 'max:20'],
            'members' => ['required', 'array', 'min:1', 'max:10'],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.school_name' => ['nullable', 'string', 'max:255'],
            'members.*.nisn' => ['nullable', 'string', 'max:20'],
            'members.*.gender' => ['required', 'in:L,P'],
            'members.*.birth_place' => ['nullable', 'string', 'max:100'],
            'members.*.birth_date' => ['nullable', 'date'],
            'members.*.phone' => ['nullable', 'string', 'max:20'],
            'members.*.role_in_team' => ['nullable', 'string', 'max:100'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,zip', 'max:5120'],
            'payment_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'target_class.required' => 'Kategori kelas wajib dipilih.',
            'match_type.required' => 'Kategori sektor pertandingan (Tunggal PA/PI) wajib dipilih.',
            'payment_proof.required' => 'Bukti pendaftaran / slip pembayaran wajib diunggah dalam satu kali pengiriman.',
            'team_name.required' => 'Nama regu / tim wajib diisi.',
            'institution_name.required' => 'Nama sekolah/madrasah asal wajib diisi.',
            'members.required' => 'Data anggota peserta wajib diisi.',
            'members.*.full_name.required' => 'Nama lengkap peserta wajib diisi.',
            'members.*.gender.required' => 'Jenis kelamin peserta wajib dipilih.',
        ]);

        // Prevent duplicate registration for member with same NISN in the SAME competition
        foreach ($validated['members'] as $memberData) {
            if (!empty($memberData['nisn'])) {
                $checkNisn = trim($memberData['nisn']);
                $existingMember = RegistrationMember::where('nisn', $checkNisn)
                    ->whereHas('registration', function($q) use ($competition) {
                        $q->where('competition_id', $competition->id)
                          ->whereIn('status', ['pending', 'verified']);
                    })
                    ->first();

                if ($existingMember) {
                    return back()->withErrors([
                        'members' => "Peserta dengan NISN '{$checkNisn}' ({$existingMember->full_name}) sudah terdaftar pada cabang lomba {$competition->name}."
                    ])->withInput();
                }
            }
        }

        // Generate Registration Code: REG-YEAR-CODE-RANDOM
        $regCode = 'REG-' . date('Y') . '-' . $competition->code . '-' . strtoupper(Str::random(5));

        $docPath = null;
        if ($request->hasFile('document_file')) {
            $docPath = $request->file('document_file')->store('documents', 'public');
        }

        $paymentPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentPath = $request->file('payment_proof')->store('payments', 'public');
        }

        $targetClass = $validated['target_class'] ?? null;
        $subCategory = null;
        if ($isBuluTangkis) {
            if ($isGandaBlt) {
                $targetClass = 'Ganda (Semua Kelas)';
                $subCategory = $validated['match_type'] ?? 'Ganda';
            } else if (!empty($validated['target_class']) && !empty($validated['match_type'])) {
                $subCategory = $validated['target_class'] . ' - ' . $validated['match_type'];
            }
        } elseif ($isTenisMeja) {
            if (!empty($validated['target_class']) && !empty($validated['match_type'])) {
                $subCategory = $validated['target_class'] . ' - ' . $validated['match_type'];
            }
        }

        $teamName = $validated['team_name'] ?? null;
        if ($isBuluTangkis && $isGandaBlt && empty($teamName)) {
            $memberNames = array_column($validated['members'], 'full_name');
            $teamName = implode(' / ', array_filter($memberNames));
        }

        $institutionName = $validated['institution_name'] ?? null;
        if ($isBuluTangkis && $isGandaBlt) {
            $memberSchools = array_unique(array_filter(array_column($validated['members'], 'school_name')));
            if (!empty($memberSchools)) {
                $institutionName = implode(' / ', $memberSchools);
            }
        }
        if (empty($institutionName)) {
            $institutionName = $validated['institution_name'] ?? $user->institution_name ?? 'Kontingen Mandiri';
        }

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'registration_code' => $regCode,
            'team_name' => $teamName,
            'sub_category' => $subCategory,
            'target_class' => $targetClass,
            'match_type' => $validated['match_type'] ?? null,
            'institution_name' => $institutionName,
            'official_name' => $validated['official_name'] ?? $user->name,
            'official_phone' => $validated['official_phone'] ?? $user->phone,
            'status' => 'pending',
            'document_file' => $docPath,
            'payment_proof' => $paymentPath,
        ]);

        foreach ($validated['members'] as $index => $memberData) {
            RegistrationMember::create([
                'registration_id' => $registration->id,
                'full_name' => $memberData['full_name'],
                'school_name' => $memberData['school_name'] ?? $validated['institution_name'] ?? $user->institution_name ?? null,
                'nisn' => $memberData['nisn'] ?? null,
                'gender' => $memberData['gender'],
                'birth_place' => $memberData['birth_place'] ?? null,
                'birth_date' => $memberData['birth_date'] ?? null,
                'phone' => $memberData['phone'] ?? null,
                'role_in_team' => $memberData['role_in_team'] ?? ($competition->isCollective() ? 'Anggota ' . ($index + 1) : ($isGandaBlt ? 'Pemain ' . ($index + 1) : 'Peserta Utama')),
            ]);
        }

        // Trigger Auto WhatsApp Notification: Pengiriman Pendaftaran Lomba (Multi-Notifikasi: Peserta, PIC, dan Bendahara)
        try {
            $firstMember = $registration->members->first();
            $targetPhone = $registration->official_phone ?: ($user->phone ?: $firstMember?->phone);
            
            // 1. Ke Pendaftar / Peserta
            \App\Services\WablasNotificationService::sendAutoNotification('registration_submitted', [
                'phone' => $targetPhone,
                'nama_peserta' => $registration->display_name,
                'nisn' => $firstMember?->nisn ?? ($user->nisn ?? '-'),
                'nama_sekolah' => $registration->institution_name,
                'cabang_lomba' => $competition->name,
                'kode_pendaftaran' => $registration->registration_code,
                'link_login' => route('peserta.registration.detail', $registration->id),
            ]);

            // 2. Ke PIC Cabang Lomba (Pendaftar Masuk & Siap Diverifikasi)
            \App\Services\WablasNotificationService::notifyPicNewRegistration($registration);

            // 3. Ke Bendahara Panitia (Pembayaran Masuk & Cek Mutasi Rekening)
            \App\Services\WablasNotificationService::notifyTreasurerNewPayment($registration, $fee);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return redirect()->route('peserta.registration.detail', $registration->id)
            ->with('success', 'Pendaftaran berhasil dikirim! Kode Pendaftaran Anda: ' . $regCode . '. Tim panitia akan memverifikasi berkas Anda.');
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
            $registration->document_file = $docPath;
        }

        if ($request->hasFile('payment_proof')) {
            $paymentPath = $request->file('payment_proof')->store('payments', 'public');
            $registration->payment_proof = $paymentPath;
        }

        $registration->status = 'pending';
        $registration->verification_notes = 'Revisi berkas telah diunggah oleh peserta pada ' . now()->format('d M Y H:i');
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
