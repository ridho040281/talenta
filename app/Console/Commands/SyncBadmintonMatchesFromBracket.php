<?php

namespace App\Console\Commands;

use App\Http\Controllers\TournamentBracketController;
use App\Models\BadmintonMatch;
use App\Models\Competition;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('badminton:sync-bracket {competition_id? : ID Kompetisi Bulu Tangkis} {--pool= : Kunci pool tertentu, contoh: kat_c_pa} {--dry-run : Pratinjau perubahan tanpa menyimpan ke database} {--force : Langsung eksekusi tanpa konfirmasi}')]
#[Description('Sinkronisasi pasangan pemain dan jadwal pertandingan (badminton_matches) agar 100% sama dengan Bagan Klasik BWF')]
class SyncBadmintonMatchesFromBracket extends Command
{
    public function handle(): int
    {
        $this->info('=== SINKRONISASI JADWAL & PARTAI BULU TANGKIS DENGAN BAGAN KLASIK BWF ===');

        $compId = $this->argument('competition_id');
        $competition = null;

        if ($compId) {
            $competition = Competition::with(['category', 'registrations.members'])->find($compId);
        } else {
            $competition = Competition::with(['category', 'registrations.members'])
                ->where('code', 'BLT')
                ->orWhere('name', 'like', '%Bulu Tangkis%')
                ->orWhere('name', 'like', '%Badminton%')
                ->first();
        }

        if (! $competition) {
            $this->error('Kompetisi Bulu Tangkis tidak ditemukan.');

            return 1;
        }

        $this->line("Kompetisi: <comment>{$competition->name}</comment> (ID: {$competition->id})");

        $targetPool = $this->option('pool');
        $isDryRun = (bool) $this->option('dry-run');

        $controller = app(TournamentBracketController::class);

        // Buat schedule plan multi-hari resmi berbasis pohon BWF (Bagan Klasik)
        $inputParams = [
            'scope' => ! empty($targetPool) ? 'pool' : 'all',
            'pool_key' => $targetPool,
        ];

        // Ambil pengaturan yang ada jika tersimpan
        $settings = $competition->bracket_settings ?? [];
        if (! empty($settings['schedule_config'])) {
            $inputParams = array_merge($inputParams, $settings['schedule_config']);
        }

        $this->line('Menghitung struktur bagan BWF dan jadwal pertandingan...');
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildMultiDaySchedulePlan');
        $method->setAccessible(true);
        $plan = $method->invoke($controller, $competition, $inputParams);

        if (empty($plan['matches'])) {
            $this->error('Tidak ada pertandingan yang ditemukan dalam bagan.');

            return 1;
        }

        $planMatches = $plan['matches'];
        $this->info('Ditemukan <comment>'.count($planMatches).'</comment> pertandingan dalam rencana Bagan Klasik.');

        $existingMatches = BadmintonMatch::where('competition_id', $competition->id)
            ->get()
            ->keyBy('match_code');

        $protectedCount = 0;
        $updatedCount = 0;
        $createdCount = 0;

        $tableRows = [];

        foreach ($planMatches as $m) {
            $code = $m['match_code'];
            $existing = $existingMatches->get($code);

            $isProtected = ($existing && in_array($existing->match_status, ['ongoing', 'finished']));

            if ($isProtected) {
                $protectedCount++;
                $tableRows[] = [
                    $code,
                    $existing->round_name ?? $m['round_name'],
                    $existing->team1_player1." ({$existing->team1_school})",
                    $existing->team2_player1." ({$existing->team2_school})",
                    strtoupper($existing->match_status).' (SKOR AMAN)',
                ];
            } else {
                if ($existing) {
                    $updatedCount++;
                } else {
                    $createdCount++;
                }

                $t1Text = ($m['team1_player'] ?? 'Menunggu Pemenang').' ('.($m['team1_school'] ?? 'TBD').')';
                $t2Text = ($m['team2_player'] ?? 'Menunggu Pemenang').' ('.($m['team2_school'] ?? 'TBD').')';

                $tableRows[] = [
                    $code,
                    $m['round_name'],
                    $t1Text,
                    $t2Text,
                    $existing ? 'DIPERBARUI (UPCOMING)' : 'BARU (UPCOMING)',
                ];
            }
        }

        $this->table(['Match Code', 'Babak', 'Tim 1 (Pemain 1)', 'Tim 2 (Pemain 2)', 'Status Sinkronisasi'], $tableRows);

        $this->line('');
        $this->info('Ringkasan Analisa:');
        $this->line(" - Partai Dilindungi (Sudah Main/Selesai, Skor Aman 100%): <comment>{$protectedCount}</comment>");
        $this->line(" - Partai Diperbarui (Belum Main, Pasangan Disamakan Bagan): <comment>{$updatedCount}</comment>");
        $this->line(" - Partai Baru: <comment>{$createdCount}</comment>");

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN aktif: Tidak ada perubahan yang disimpan ke database.');

            return 0;
        }

        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin menyinkronkan data pertandingan sekarang?', true)) {
            $this->warn('Sinkronisasi dibatalkan oleh pengguna.');

            return 0;
        }

        // Eksekusi pembaruan ke database
        $syncedCount = 0;
        DB::transaction(function () use ($competition, $planMatches, $existingMatches, $targetPool, &$syncedCount) {
            foreach ($planMatches as $m) {
                $existingRecord = $existingMatches->get($m['match_code']);

                $status = $m['status'];
                $winnerTeam = $m['winner_team'];
                if ($existingRecord && in_array($existingRecord->match_status, ['ongoing', 'finished'])) {
                    $status = $existingRecord->match_status;
                    $winnerTeam = $existingRecord->winner_team;
                }

                $team1Player = $m['team1_player'] ?? 'Menunggu Pemenang';
                $team1School = $m['team1_school'] ?? 'TBD';
                $team1Id = $m['team1_id'] ?? null;
                if ($existingRecord && in_array($existingRecord->match_status, ['ongoing', 'finished']) && ! empty($existingRecord->team1_registration_id)) {
                    $team1Id = $existingRecord->team1_registration_id;
                    $team1School = $existingRecord->team1_school;
                    $team1Player = $existingRecord->team1_player1;
                }

                $team2Player = $m['team2_player'] ?? 'Menunggu Pemenang';
                $team2School = $m['team2_school'] ?? 'TBD';
                $team2Id = $m['team2_id'] ?? null;
                if ($existingRecord && in_array($existingRecord->match_status, ['ongoing', 'finished']) && ! empty($existingRecord->team2_registration_id)) {
                    $team2Id = $existingRecord->team2_registration_id;
                    $team2School = $existingRecord->team2_school;
                    $team2Player = $existingRecord->team2_player1;
                }

                $attributes = [
                    'court_number' => $m['court_number'],
                    'scheduled_time' => $m['scheduled_time'],
                    'match_order' => $m['match_order'],
                    'match_day' => $m['match_day'],
                    'match_date' => $m['match_date'],
                    'match_day_label' => $m['match_day_label'],
                    'round_name' => $m['round_name'],
                    'category' => $m['category'],
                    'match_type' => $m['match_type'],
                    'team1_registration_id' => $team1Id,
                    'team1_school' => $team1School,
                    'team1_player1' => $team1Player,
                    'team2_registration_id' => $team2Id,
                    'team2_school' => $team2School,
                    'team2_player1' => $team2Player,
                    'match_status' => $status,
                    'winner_team' => $winnerTeam,
                ];

                if ($existingRecord) {
                    $existingRecord->fill($attributes)->save();
                } else {
                    BadmintonMatch::create(array_merge([
                        'competition_id' => $competition->id,
                        'match_code' => $m['match_code'],
                    ], $attributes));
                }

                $syncedCount++;
            }

            // Bersihkan match berstatus 'upcoming' lama jika match_code tidak ada lagi dalam bagan
            $planCodes = collect($planMatches)->pluck('match_code')->filter()->all();
            if (! empty($planCodes)) {
                $purgeQuery = BadmintonMatch::where('competition_id', $competition->id)
                    ->where('match_status', 'upcoming');
                if (! empty($targetPool)) {
                    $purgeQuery->where('match_code', 'like', "{$targetPool}-%");
                }
                $purgeQuery->whereNotIn('match_code', $planCodes)->delete();
            }
        });

        $this->info("✓ BERHASIL: {$syncedCount} pertandingan berhasil disinkronkan ke database dengan aman!");

        return 0;
    }
}
