<?php

namespace App\Console\Commands;

use App\Http\Controllers\CollectiveRegistrationController;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('talenta:sync-pramuka-photos')]
#[Description('Ekstrak file ZIP foto peserta Pramuka yang sudah terunggah dan sematkan ke data anggota masing-masing')]
class SyncPramukaZipPhotos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi foto dari direktori ZIP Pramuka...');

        $zipFiles = Storage::disk('public')->files('pramuka-photos');
        if (empty($zipFiles)) {
            $this->warn('Tidak ada file ZIP ditemukan di direktori storage/pramuka-photos.');

            return Command::SUCCESS;
        }

        $this->info('Ditemukan '.count($zipFiles).' file ZIP di direktori storage/pramuka-photos.');

        $totalAssigned = 0;
        foreach ($zipFiles as $zipFile) {
            $this->line("Memproses: {$zipFile}...");
            $assigned = CollectiveRegistrationController::extractAndAssignZipPhotos($zipFile);
            $this->info(" -> Berhasil menyematkan {$assigned} foto peserta.");
            $totalAssigned += $assigned;
        }

        $this->info("Selesai! Total {$totalAssigned} foto peserta berhasil diekstrak dan disematkan.");

        return Command::SUCCESS;
    }
}
