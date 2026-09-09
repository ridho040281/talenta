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
        Schema::table('competitions', function (Blueprint $table) {
            if (!Schema::hasColumn('competitions', 'registration_start_at')) {
                $table->dateTime('registration_start_at')->nullable()->after('schedule_time');
            }
            if (!Schema::hasColumn('competitions', 'registration_end_at')) {
                $table->dateTime('registration_end_at')->nullable()->after('registration_start_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            if (Schema::hasColumn('competitions', 'registration_end_at')) {
                $table->dropColumn('registration_end_at');
            }
            if (Schema::hasColumn('competitions', 'registration_start_at')) {
                $table->dropColumn('registration_start_at');
            }
        });
    }
};
