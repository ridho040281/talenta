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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('peserta'); // juara, peserta, pembimbing, juri
            $table->foreignId('competition_id')->nullable()->constrained('competitions')->nullOnDelete();
            $table->string('background_path')->nullable();
            $table->json('layout_config')->nullable();
            $table->string('number_format')->default('[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
