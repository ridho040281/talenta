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
        Competition::where('code', 'ROB')
            ->orWhere('name', 'LIKE', '%Robotik%')
            ->orWhere('name', 'LIKE', '%Robotic%')
            ->update([
                'type' => 'kolektif',
                'min_members' => 2,
                'max_members' => 3,
            ]);

        Competition::where('code', 'PRM')
            ->orWhere('name', 'LIKE', '%Pramuka%')
            ->update([
                'type' => 'kolektif',
                'min_members' => 4,
                'max_members' => 6,
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
