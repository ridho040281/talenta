<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_pics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_title')->default('PIC Lomba');
            $table->timestamps();

            $table->unique(['competition_id', 'user_id']);
        });

        // Migrate existing pic_id from competitions to competition_pics
        $competitions = DB::table('competitions')->whereNotNull('pic_id')->get();
        foreach ($competitions as $comp) {
            DB::table('competition_pics')->insertOrIgnore([
                'competition_id' => $comp->id,
                'user_id' => $comp->pic_id,
                'role_title' => 'Koordinator PIC',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_pics');
    }
};
