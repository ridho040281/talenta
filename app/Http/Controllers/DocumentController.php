<?php

namespace App\Http\Controllers;

use App\Models\Competition;
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
            $managedCompIds = PicController::getManagedCompetitionIds($user);
            if (! in_array($registration->competition_id, $managedCompIds)) {
                abort(403, 'Anda tidak memiliki akses sebagai PIC untuk pendaftaran ini.');
            }
        }
        // Superadmin and Panitia have full access

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

    /**
     * 5. Cetak Kartu Tanda Peserta (ID Card) - Satuan atau Per Regu
     */
    public function printIdCard($registration_id)
    {
        $registration = Registration::with(['user', 'competition.category', 'members', 'verifier'])->findOrFail($registration_id);
        $redirect = $this->checkAccess($registration, true);
        if ($redirect) {
            return $redirect;
        }

        return view('documents.print-idcard', compact('registration'));
    }

    /**
     * 6. Cetak Massal Semua Kartu Peserta Cabang Lomba (A4 Grid 4 Kartu)
     */
    public function printAllCompetitionIdCards(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $competitionId = $request->query('competition_id');
        if (! $competitionId) {
            return back()->with('error', 'Cabang lomba tidak ditemukan.');
        }

        $competition = Competition::with('category')->findOrFail($competitionId);

        // Security check for PIC
        if ($user->role === 'pic_lomba') {
            $managedCompIds = PicController::getManagedCompetitionIds($user);
            if (! in_array($competition->id, $managedCompIds)) {
                abort(403, 'Anda tidak memiliki akses sebagai PIC untuk cabang lomba ini.');
            }
        }

        $query = Registration::with(['user', 'competition.category', 'members', 'verifier'])
            ->where('competition_id', $competition->id)
            ->where('status', 'verified');

        if ($request->filled('gender') && $request->query('gender') !== 'all') {
            $query->where('primary_gender', $request->query('gender'));
        }

        if ($request->filled('category_class') && $request->query('category_class') !== 'all') {
            $catClass = strtolower($request->query('category_class'));
            if ($competition->code === 'ROB') {
                $query->where(function ($q) use ($catClass) {
                    $q->where('sub_category', 'like', "%{$catClass}%")
                        ->orWhere('target_class', 'like', "%{$catClass}%");
                });
            } else {
                $query->where('target_class', 'like', "%{$catClass}%");
            }
        }

        $registrations = $query->orderBy('draw_number', 'asc')
            ->orderBy('participant_number', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('documents.print-all-idcards', compact('competition', 'registrations'));
    }
}
