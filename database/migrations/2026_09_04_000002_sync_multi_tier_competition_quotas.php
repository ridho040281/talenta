<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sync multi-tier competition quotas to match tier configuration
        DB::table('competitions')->where('code', 'MTQ')->update(['quota' => 100]);
        DB::table('competitions')->where('code', 'POP')->update(['quota' => 60]);
        DB::table('competitions')->where('code', 'TMJ')->update(['quota' => 40]);
        DB::table('competitions')->where('code', 'BLT')->update(['quota' => 112]);
    }

    public function down(): void
    {
        // No-op
    }
};
