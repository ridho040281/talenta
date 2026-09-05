<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    protected function checkAccess($registration, $requireVerified = false)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        if ($user->role === 'peserta') {
            if ($registration->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke dokumen pendaftaran ini.');
            }

            if ($requireVerified && $registration->status !== 'verified') {
                return redirect()->route('peserta.registration.detail', $registration->id)
                    ->with('error', 'Peringatan: Pendaftaran Anda belum terverifikasi! Anda tidak bisa mencetak berkas ini sebelum status pendaftaran diverifikasi oleh panitia.');
            }
        } elseif ($user->role === 'pic_lomba') {
            if ($registration->competition && $registration->competition->pic_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses sebagai PIC untuk pendaftaran ini.');
            }
        }
        // Superadmin has full access

        return null;
    }

    /**
     * 1. Cetak Bukti Akun Pendaftar
     */
    public function printAccountProof($registration_id)
    {
        $registration = Registration::with(['user', 'competition.category', 'members', 'invoice'])->findOrFail($registration_id);
        $redirect = $this->checkAccess($registration, false);
        if ($redirect) {
            return $redirect;
        }

        return view('documents.print-account-proof', compact('registration'));
    }

    /**
     * 2. Cetak Bukti Pendaftaran & Kartu Peserta (Biodata, No Peserta, Status, Cabang)
     */
    public function printRegistrationForm($registration_id)
    {
        $registration = Registration::with(['user', 'competition.category', 'members', 'verifier'])->findOrFail($registration_id);
        $redirect = $this->checkAccess($registration, true);
        if ($redirect) {
            return $redirect;
        }

        return view('documents.print-registration-form', compact('registration'));
    }

    /**
     * 3. Cetak Kwitansi / Invoice Pembayaran Resmi
     */
    public function printReceipt($registration_id)
    {
        $registration = Registration::with(['user', 'competition', 'invoice.registrations.competition', 'members'])->findOrFail($registration_id);
        $redirect = $this->checkAccess($registration, true);
        if ($redirect) {
            return $redirect;
        }

        return view('documents.print-receipt', compact('registration'));
    }

    /**
     * 4. Cetak Kolektif Semua Bukti Pendaftaran Peserta (Multi-Page PDF)
     */
    public function printCollectiveRegistrations(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $targetUserId = $user->id;
        if ($user->role === 'superadmin' && $request->has('user_id')) {
            $targetUserId = $request->query('user_id');
        }

        // Ambil seluruh pendaftaran milik user yang terverifikasi (atau seluruhnya jika ada)
        $registrations = Registration::with(['user', 'competition.category', 'members', 'verifier'])
            ->where('user_id', $targetUserId)
            ->where('status', 'verified')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($registrations->isEmpty()) {
            $registrations = Registration::with(['user', 'competition.category', 'members', 'verifier'])
                ->where('user_id', $targetUserId)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        if ($registrations->isEmpty()) {
            return redirect()->route('peserta.dashboard')
                ->with('error', 'Belum ada pendaftaran yang dapat dicetak secara kolektif.');
        }

        return view('documents.print-collective-registrations', compact('registrations'));
    }
}
