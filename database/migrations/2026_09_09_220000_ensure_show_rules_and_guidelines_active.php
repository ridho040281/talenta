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
        DB::table('competitions')
            ->whereNotNull('rules')
            ->where('rules', '!=', '')
            ->update(['show_rules' => true]);

        DB::table('competitions')
            ->whereNotNull('guidelines_file')
            ->where('guidelines_file', '!=', '')
            ->update(['show_guidelines' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
