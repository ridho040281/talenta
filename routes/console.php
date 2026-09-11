<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('storage:compress-existing', function () {
    $this->info('=== Memulai Kompresi File Lama di Storage ===');

    $targets = [
        [
            'label' => 'Bukti Transfer (payments)',
            'folder' => 'payments',
            'max_dim' => 1200,
            'target_kb' => 100,
            'db_type' => 'payment_proof',
        ],
        [
            'label' => 'Bukti Transfer Invoice (payment_proofs)',
            'folder' => 'payment_proofs',
            'max_dim' => 1200,
            'target_kb' => 100,
            'db_type' => 'payment_proof',
        ],
        [
            'label' => 'Foto Pembina / Official Pramuka',
            'folder' => 'photos/pramuka/officials',
            'max_dim' => 1080,
            'target_kb' => 200,
            'db_type' => 'official_photo',
        ],
        [
            'label' => 'Foto Anggota Regu Pramuka',
            'folder' => 'photos/pramuka/members',
            'max_dim' => 1080,
            'target_kb' => 200,
            'db_type' => 'member_photo',
        ],
    ];

    $totalProcessed = 0;
    $totalSavedBytes = 0;
    $totalSkipped = 0;

    foreach ($targets as $cfg) {
        $folderPath = storage_path('app/public/'.$cfg['folder']);
        $this->newLine();
        $this->line("<fg=yellow;options=bold>Memeriksa folder:</> {$cfg['label']} [{$cfg['folder']}]");

        if (! file_exists($folderPath)) {
            $this->line('  -> Folder belum ada di server, dilewati.');
            continue;
        }

        $files = glob($folderPath.'/*.*');
        if (empty($files)) {
            $this->line('  -> Tidak ada file dalam folder ini.');
            continue;
        }

        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                $this->line("  - [PDF] {$fileName} (dilewati)");
                $totalSkipped++;
                continue;
            }

            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp'])) {
                continue;
            }

            $originalSize = filesize($filePath);
            $targetBytes = $cfg['target_kb'] * 1024;

            if ($originalSize <= $targetBytes) {
                $kb = round($originalSize / 1024, 1);
                $this->line("  - [AMAN] {$fileName} ({$kb} KB <= {$cfg['target_kb']} KB)");
                $totalSkipped++;
                continue;
            }

            $origKb = round($originalSize / 1024, 1);
            $this->comment("  - [KOMPRES] {$fileName} ({$origKb} KB) -> target {$cfg['target_kb']} KB...");

            $oldRelPath = $cfg['folder'].'/'.$fileName;

            $newRelPath = \App\Services\ImageOptimizerService::optimizeAndStore(
                $filePath,
                $cfg['folder'],
                $fileName,
                $cfg['max_dim'],
                $cfg['target_kb']
            );

            $newFullPath = storage_path('app/public/'.$newRelPath);
            $newSize = file_exists($newFullPath) ? filesize($newFullPath) : $originalSize;
            $newKb = round($newSize / 1024, 1);
            $saved = $originalSize - $newSize;
            if ($saved > 0) {
                $totalSavedBytes += $saved;
            }

            if ($oldRelPath !== $newRelPath && file_exists($newFullPath)) {
                $dbUpdated = true;
                try {
                    if ($cfg['db_type'] === 'payment_proof') {
                        \App\Models\Registration::where('payment_proof', $oldRelPath)->update(['payment_proof' => $newRelPath]);
                        \App\Models\Invoice::where('payment_proof', $oldRelPath)->update(['payment_proof' => $newRelPath]);
                    } elseif ($cfg['db_type'] === 'official_photo') {
                        \App\Models\Registration::where('official_photo', $oldRelPath)->update(['official_photo' => $newRelPath]);
                    } elseif ($cfg['db_type'] === 'member_photo') {
                        \App\Models\RegistrationMember::where('photo', $oldRelPath)->update(['photo' => $newRelPath]);
                    }
                } catch (\Throwable $e) {
                    $dbUpdated = false;
                    $this->warn('    (Catatan DB: '.$e->getMessage().')');
                }

                if ($dbUpdated) {
                    @unlink($filePath);
                    @unlink(public_path('storage/'.$oldRelPath));
                }
            }

            if (class_exists(\App\Http\Controllers\AdminSettingsController::class)) {
                \App\Http\Controllers\AdminSettingsController::ensurePublicStorageSync($newRelPath);
            }

            $this->info("    ✓ Selesai: {$origKb} KB -> {$newKb} KB (Hemat: ".round($saved / 1024, 1).' KB)');
            $totalProcessed++;
        }
    }

    $this->newLine();
    $totalMbSaved = round($totalSavedBytes / (1024 * 1024), 2);
    $this->info('=== Selesai! ===');
    $this->info("Total file berhasil dikompres : {$totalProcessed}");
    $this->info("Total file dilewati (sudah kecil / PDF) : {$totalSkipped}");
    $this->info("Total ruang disk yang dihemat : {$totalMbSaved} MB");
})->purpose('Kompres berkas lama (foto pramuka dan bukti transfer) yang ukurannya melebihi batas');

