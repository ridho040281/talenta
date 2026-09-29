<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update any competition whose schedule_date is set to 2026-09-28
        if (DB::getSchemaBuilder()->hasTable('competitions')) {
            DB::table('competitions')
                ->where('schedule_date', '2026-09-28')
                ->update(['schedule_date' => '2026-09-29']);
        }

        // 2. Correct match_date and match_day_label in badminton_matches based on match_day
        // Hanya memperbarui tanggal & label hari, tanpa mengubah data lapangan, jam, partai, skor, ataupun pemenang!
        if (DB::getSchemaBuilder()->hasTable('badminton_matches')) {
            $daysMapping = [
                1 => ['date' => '2026-09-29', 'label' => 'Hari 1 (Selasa, 29 Sep 2026)'],
                2 => ['date' => '2026-09-30', 'label' => 'Hari 2 (Rabu, 30 Sep 2026)'],
                3 => ['date' => '2026-10-01', 'label' => 'Hari 3 (Kamis, 1 Okt 2026)'],
                4 => ['date' => '2026-10-02', 'label' => 'Hari 4 (Jumat, 2 Okt 2026)'],
            ];

            foreach ($daysMapping as $day => $info) {
                DB::table('badminton_matches')
                    ->where('match_day', $day)
                    ->update([
                        'match_date' => $info['date'],
                        'match_day_label' => $info['label'],
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reversal needed
    }
};
