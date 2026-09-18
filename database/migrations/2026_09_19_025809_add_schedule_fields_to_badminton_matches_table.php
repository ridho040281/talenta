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
            $table->string('scheduled_time', 10)->nullable()->after('court_number');
            $table->unsignedSmallInteger('match_order')->nullable()->after('scheduled_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badminton_matches', function (Blueprint $table) {
            $table->dropColumn(['scheduled_time', 'match_order']);
        });
    }
};
