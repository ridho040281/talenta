<?php

use App\Models\Competition;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Competition::where('code', 'PRM')
            ->orWhere('name', 'LIKE', '%Pramuka%')
            ->orWhere('slug', 'LIKE', '%pramuka%')
            ->update([
                'type' => 'kolektif',
                'min_members' => 8,
                'max_members' => 8,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
