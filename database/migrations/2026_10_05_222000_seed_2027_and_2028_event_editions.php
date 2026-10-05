<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('event_editions')) {
            $existingYears = DB::table('event_editions')->pluck('year')->toArray();

            if (! in_array('2026', $existingYears)) {
                DB::table('event_editions')->insert([
                    'year' => '2026',
                    'event_name' => 'Milad ke-57 MTsN 1 Blitar',
                    'is_active' => true,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (! in_array('2027', $existingYears)) {
                DB::table('event_editions')->insert([
                    'year' => '2027',
                    'event_name' => 'Milad ke-58 MTsN 1 Blitar',
                    'is_active' => false,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (! in_array('2028', $existingYears)) {
                DB::table('event_editions')->insert([
                    'year' => '2028',
                    'event_name' => 'Milad ke-59 MTsN 1 Blitar',
                    'is_active' => false,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve user data
    }
};
