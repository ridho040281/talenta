<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->boolean('has_stage_timer')->default(false)->after('is_live_score');
            $table->integer('stage_duration_minutes')->default(7)->after('has_stage_timer');
            $table->integer('stage_warning_minutes')->default(2)->after('stage_duration_minutes');
            $table->integer('stage_overtime_minutes')->default(1)->after('stage_warning_minutes');
            $table->string('stage_bell_sound')->default('bell')->after('stage_overtime_minutes');
            $table->json('stage_state')->nullable()->after('stage_bell_sound');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('stage_status')->default('waiting')->after('status');
            $table->integer('stage_duration_seconds')->nullable()->after('stage_status');
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn([
                'has_stage_timer',
                'stage_duration_minutes',
                'stage_warning_minutes',
                'stage_overtime_minutes',
                'stage_bell_sound',
                'stage_state',
            ]);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'stage_status',
                'stage_duration_seconds',
            ]);
        });
    }
};
