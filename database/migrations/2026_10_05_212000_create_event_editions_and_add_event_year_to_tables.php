<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create event_editions table
        if (! Schema::hasTable('event_editions')) {
            Schema::create('event_editions', function (Blueprint $table) {
                $table->id();
                $table->string('year', 10)->unique();
                $table->string('event_name', 255);
                $table->string('theme_slogan', 255)->nullable();
                $table->boolean('is_active')->default(false);
                $table->string('status', 20)->default('open');
                $table->timestamps();
            });

            // Seed initial 2026 edition
            DB::table('event_editions')->insert([
                'year' => '2026',
                'event_name' => 'Milad ke-57 MTsN 1 Blitar',
                'is_active' => true,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Add event_year column to registrations table
        if (! Schema::hasColumn('registrations', 'event_year')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->string('event_year', 10)->default('2026')->after('id')->index();
            });
        }

        // 3. Add event_year column to invoices table
        if (! Schema::hasColumn('invoices', 'event_year')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('event_year', 10)->default('2026')->after('id')->index();
            });
        }

        // 4. Add event_year column to badminton_matches table if it exists
        if (Schema::hasTable('badminton_matches') && ! Schema::hasColumn('badminton_matches', 'event_year')) {
            Schema::table('badminton_matches', function (Blueprint $table) {
                $table->string('event_year', 10)->default('2026')->after('id')->index();
            });
        }

        // 5. Update all existing data to 2026
        DB::table('registrations')->whereNull('event_year')->orWhere('event_year', '')->update(['event_year' => '2026']);
        DB::table('invoices')->whereNull('event_year')->orWhere('event_year', '')->update(['event_year' => '2026']);
        if (Schema::hasTable('badminton_matches')) {
            DB::table('badminton_matches')->whereNull('event_year')->orWhere('event_year', '')->update(['event_year' => '2026']);
        }

        // 6. Ensure app_settings defaults
        DB::table('app_settings')->updateOrInsert(['key' => 'event_year'], ['value' => '2026', 'group' => 'general']);
        DB::table('app_settings')->updateOrInsert(['key' => 'event_name'], ['value' => 'Milad ke-57 MTsN 1 Blitar', 'group' => 'general']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('badminton_matches') && Schema::hasColumn('badminton_matches', 'event_year')) {
            Schema::table('badminton_matches', function (Blueprint $table) {
                $table->dropColumn('event_year');
            });
        }

        if (Schema::hasColumn('invoices', 'event_year')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('event_year');
            });
        }

        if (Schema::hasColumn('registrations', 'event_year')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropColumn('event_year');
            });
        }

        Schema::dropIfExists('event_editions');
    }
};
