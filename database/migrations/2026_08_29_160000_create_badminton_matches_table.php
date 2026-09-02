<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badminton_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->nullable()->constrained('competitions')->nullOnDelete();
            $table->string('court_number')->default('Lapangan 1');
            $table->string('match_code')->nullable();
            $table->string('round_name')->default('Penyisihan');
            $table->string('category')->default('MS');
            $table->enum('match_type', ['single', 'double'])->default('single');
            
            // Tim 1 (Sisi Atas / Team A)
            $table->foreignId('team1_registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->string('team1_school');
            $table->string('team1_player1');
            $table->string('team1_player2')->nullable();

            // Tim 2 (Sisi Bawah / Team B)
            $table->foreignId('team2_registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->string('team2_school');
            $table->string('team2_player1');
            $table->string('team2_player2')->nullable();

            // Skor & Posisi Game Live
            $table->unsignedTinyInteger('current_set')->default(1);
            $table->unsignedTinyInteger('team1_set1')->default(0);
            $table->unsignedTinyInteger('team2_set1')->default(0);
            $table->unsignedTinyInteger('team1_set2')->default(0);
            $table->unsignedTinyInteger('team2_set2')->default(0);
            $table->unsignedTinyInteger('team1_set3')->default(0);
            $table->unsignedTinyInteger('team2_set3')->default(0);

            $table->unsignedTinyInteger('server_team')->default(1);
            $table->unsignedTinyInteger('server_player')->default(1);
            $table->enum('match_status', ['upcoming', 'ongoing', 'interval', 'finished'])->default('upcoming');
            $table->unsignedTinyInteger('winner_team')->nullable();

            $table->foreignId('umpire_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('scores_history')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badminton_matches');
    }
};