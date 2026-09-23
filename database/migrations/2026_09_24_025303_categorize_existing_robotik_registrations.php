<?php

use App\Models\Competition;
use App\Models\Registration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $robotikCompetitions = Competition::where('code', 'ROB')
            ->orWhere('name', 'LIKE', '%Robotik%')
            ->orWhere('name', 'LIKE', '%Robotic%')
            ->get();

        foreach ($robotikCompetitions as $comp) {
            $registrations = Registration::where('competition_id', $comp->id)->get();

            foreach ($registrations as $reg) {
                $blob = strtolower(($reg->match_type ?? '').' '.($reg->sub_category ?? '').' '.($reg->target_class ?? '').' '.($reg->verification_notes ?? ''));

                if (str_contains($blob, 'sumo')) {
                    $cat = 'Sumo';
                } elseif (str_contains($blob, 'soccer')) {
                    $cat = 'Soccer';
                } else {
                    $cat = 'Kreatif';
                }

                $reg->match_type = $cat;
                $reg->sub_category = 'Robotik '.$cat;
                $reg->target_class = 'Robotik '.$cat;
                $reg->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op as categorizing preserves registration integrity
    }
};
