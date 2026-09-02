<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('date_label'); // contoh: "01 - 15 September 2026"
            $table->string('time_label')->nullable(); // contoh: "Pukul 08.00 WIB"
            $table->string('location')->nullable(); // contoh: "Portal Online / Aula MTsN 1 Blitar"
            $table->text('description')->nullable();
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timelines');
    }
};
