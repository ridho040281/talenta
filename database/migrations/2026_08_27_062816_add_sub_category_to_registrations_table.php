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
            $table->string('sub_category', 100)->nullable()->after('team_name');
            $table->string('target_class', 50)->nullable()->after('sub_category');
            $table->string('match_type', 50)->nullable()->after('target_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['sub_category', 'target_class', 'match_type']);
        });
    }
};
