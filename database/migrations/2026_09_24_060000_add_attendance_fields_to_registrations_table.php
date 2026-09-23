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
        Schema::table('registrations', function (Blueprint $table) {
            $table->boolean('is_attended')->default(false)->after('verified_by')->index();
            $table->timestamp('attended_at')->nullable()->after('is_attended');
            $table->foreignId('attended_by')->nullable()->after('attended_at')->constrained('users')->nullOnDelete();
            $table->string('attendance_notes')->nullable()->after('attended_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['attended_by']);
            $table->dropColumn(['is_attended', 'attended_at', 'attended_by', 'attendance_notes']);
        });
    }
};
