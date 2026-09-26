<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('badminton_matches', function (Blueprint $table) {
            $table->string('court_number')->nullable()->default(null)->change();
        });

        DB::table('badminton_matches')
            ->whereNull('scheduled_time')
            ->whereNull('match_day')
            ->where('court_number', 'Lapangan 1')
            ->where(function ($q) {
                $q->whereNull('match_status')
                    ->orWhereNotIn('match_status', ['ongoing', 'finished']);
            })
            ->update(['court_number' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badminton_matches', function (Blueprint $table) {
            $table->string('court_number')->default('Lapangan 1')->change();
        });
    }
};
