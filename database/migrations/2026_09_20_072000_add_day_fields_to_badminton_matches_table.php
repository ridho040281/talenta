<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('badminton_matches', function (Blueprint $table) {
            $table->unsignedTinyInteger('match_day')->default(1)->nullable()->after('match_order');
            $table->date('match_date')->nullable()->after('match_day');
            $table->string('match_day_label', 50)->nullable()->after('match_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badminton_matches', function (Blueprint $table) {
            $table->dropColumn(['match_day', 'match_date', 'match_day_label']);
        });
    }
};
