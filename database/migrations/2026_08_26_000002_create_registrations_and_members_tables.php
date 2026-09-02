<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('registration_code')->unique();
            $table->string('participant_number')->nullable();
            $table->integer('draw_number')->nullable();
            $table->string('bracket_slot')->nullable();
            $table->string('team_name')->nullable();
            $table->string('institution_name');
            $table->string('official_name')->nullable();
            $table->string('official_phone')->nullable();
            $table->string('status')->default('pending'); // pending, verified, rejected, revision
            $table->string('payment_proof')->nullable();
            $table->string('document_file')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('registration_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('nisn')->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->string('role_in_team')->default('Peserta Utama');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_members');
        Schema::dropIfExists('registrations');
    }
};
