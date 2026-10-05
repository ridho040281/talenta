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
        Schema::table('competitions', function (Blueprint $table) {
            $table->string('card_template')->nullable()->after('rules');
            $table->string('card_title')->nullable()->after('card_template');
            $table->string('card_theme_color', 50)->nullable()->default('emerald')->after('card_title');
            $table->string('card_number_label', 50)->nullable()->after('card_theme_color');
            $table->string('card_team_label', 50)->nullable()->after('card_number_label');
            $table->string('card_school_label', 50)->nullable()->after('card_team_label');
            $table->string('card_mascot')->nullable()->after('card_school_label');
            $table->string('card_footer_text')->nullable()->after('card_mascot');
            $table->json('card_settings')->nullable()->after('card_footer_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn([
                'card_template',
                'card_title',
                'card_theme_color',
                'card_number_label',
                'card_team_label',
                'card_school_label',
                'card_mascot',
                'card_footer_text',
                'card_settings',
            ]);
        });
    }
};
