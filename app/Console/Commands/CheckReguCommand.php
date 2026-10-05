<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Models\RegistrationMember;
use Illuminate\Console\Command;

class CheckReguCommand extends Command
{
    protected $signature = 'talenta:check-regu {keyword=Sakura}';
    protected $description = 'Cek data pendaftaran dan anggota regu Pramuka di database';

    public function handle(): int
    {
        $keyword = $this->argument('keyword');
        $this->info("=== MENCARI REGISTRASI DENGAN KATA KUNCI: '{$keyword}' ===");

        $registrations = Registration::with(['competition', 'members', 'user', 'invoice'])
            ->where('team_name', 'LIKE', "%{$keyword}%")
            ->orWhere('institution_name', 'LIKE', "%{$keyword}%")
            ->orWhereHas('members', function ($q) use ($keyword) {
                $q->where('full_name', 'LIKE', "%{$keyword}%");
            })
            ->get();

        if ($registrations->isEmpty()) {
            $this->warn("Tidak ditemukan pendaftaran dengan kata kunci '{$keyword}'.");
            $this->info("Mencari 5 pendaftaran Pramuka terakhir:");
            $registrations = Registration::with(['competition', 'members', 'user', 'invoice'])
                ->whereHas('competition', function ($q) {
                    $q->where('code', 'PRM')->orWhere('name', 'LIKE', '%Pramuka%');
                })
                ->latest()
                ->take(5)
                ->get();
        }

        $this->info("Ditemukan " . $registrations->count() . " pendaftaran:");

        foreach ($registrations as $r) {
            $this->line("------------------------------------------------------------------");
            $this->info("REGISTRATION ID: {$r->id} | KODE: {$r->registration_code} | STATUS: {$r->status}");
            $this->line("Cabang Lomba  : " . ($r->competition->name ?? '-') . " (ID: {$r->competition_id})");
            $this->line("Nama Tim/Regu : " . ($r->team_name ?? '-'));
            $this->line("Pangkalan     : " . ($r->institution_name ?? '-'));
            $this->line("User / Akun   : " . ($r->user->name ?? '-') . " (User ID: {$r->user_id})");
            $this->line("Invoice ID    : " . ($r->invoice_id ?? '-'));
            $this->line("No Undian     : " . ($r->draw_number ?? '-'));
            $this->line("No Peserta    : " . ($r->participant_number ?? '-'));
            $this->line("Jumlah Member di Tabel registration_members: " . $r->members->count());

            foreach ($r->members as $idx => $m) {
                $hasPhoto = !empty($m->photo) ? 'Ada Foto' : 'Tanpa Foto';
                $this->line("  [Member " . ($idx + 1) . "] ID: {$m->id} | Nama: {$m->full_name} | Role: {$m->role_in_team} | Foto: {$hasPhoto}");
            }
        }
        $this->line("------------------------------------------------------------------");

        // Total registrations for this user or team
        if ($registrations->isNotEmpty()) {
            $first = $registrations->first();
            $sameUserRegs = Registration::where('user_id', $first->user_id)->count();
            $this->info("Total pendaftaran milik User ID {$first->user_id}: {$sameUserRegs}");
            if ($first->invoice_id) {
                $sameInvoiceRegs = Registration::where('invoice_id', $first->invoice_id)->count();
                $this->info("Total pendaftaran dalam Invoice ID {$first->invoice_id}: {$sameInvoiceRegs}");
            }
        }

        return 0;
    }
}
