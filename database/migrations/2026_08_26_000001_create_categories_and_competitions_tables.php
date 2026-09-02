<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('trophy');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->string('type')->default('individu'); // individu, kolektif
            $table->integer('min_members')->default(1);
            $table->integer('max_members')->default(1);
            $table->integer('quota')->default(50);
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->text('rules')->nullable();
            $table->string('guidelines_file')->nullable();
            $table->string('venue')->nullable();
            $table->date('schedule_date')->nullable();
            $table->string('schedule_time')->nullable();
            $table->string('status')->default('buka'); // buka, tutup, berlangsung, selesai
            $table->boolean('has_draw')->default(true);
            $table->string('draw_status')->default('pending'); // pending, completed
            $table->timestamps();
        });

        Schema::create('competition_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('name');
            $table->integer('weight_percentage')->default(100);
            $table->decimal('min_score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(100);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_criteria');
        Schema::dropIfExists('competitions');
        Schema::dropIfExists('categories');
    }
};
