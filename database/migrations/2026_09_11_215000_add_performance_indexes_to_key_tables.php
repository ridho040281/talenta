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
        // 1. Indexing table registrations
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->index('status', 'idx_registrations_status');
                $table->index('participant_number', 'idx_registrations_participant_number');
                $table->index('draw_number', 'idx_registrations_draw_number');
                $table->index('target_class', 'idx_registrations_target_class');
                $table->index('match_type', 'idx_registrations_match_type');
                $table->index(['competition_id', 'status'], 'idx_registrations_comp_status');
                $table->index(['competition_id', 'target_class', 'match_type'], 'idx_registrations_comp_class_match');
            });
        }

        // 2. Indexing table registration_members
        if (Schema::hasTable('registration_members')) {
            Schema::table('registration_members', function (Blueprint $table) {
                $table->index('nisn', 'idx_reg_members_nisn');
                $table->index('full_name', 'idx_reg_members_full_name');
                $table->index('gender', 'idx_reg_members_gender');
            });
        }

        // 3. Indexing table invoices
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index('status', 'idx_invoices_status');
                $table->index('type', 'idx_invoices_type');
            });
        }

        // 4. Indexing table draw_allocations
        if (Schema::hasTable('draw_allocations')) {
            Schema::table('draw_allocations', function (Blueprint $table) {
                $table->index(['competition_id', 'draw_number'], 'idx_draw_allocations_comp_draw');
            });
        }

        // 5. Indexing table users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('role', 'idx_users_role');
                $table->index('phone', 'idx_users_phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('registrations')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropIndex('idx_registrations_status');
                $table->dropIndex('idx_registrations_participant_number');
                $table->dropIndex('idx_registrations_draw_number');
                $table->dropIndex('idx_registrations_target_class');
                $table->dropIndex('idx_registrations_match_type');
                $table->dropIndex('idx_registrations_comp_status');
                $table->dropIndex('idx_registrations_comp_class_match');
            });
        }

        if (Schema::hasTable('registration_members')) {
            Schema::table('registration_members', function (Blueprint $table) {
                $table->dropIndex('idx_reg_members_nisn');
                $table->dropIndex('idx_reg_members_full_name');
                $table->dropIndex('idx_reg_members_gender');
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('idx_invoices_status');
                $table->dropIndex('idx_invoices_type');
            });
        }

        if (Schema::hasTable('draw_allocations')) {
            Schema::table('draw_allocations', function (Blueprint $table) {
                $table->dropIndex('idx_draw_allocations_comp_draw');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_role');
                $table->dropIndex('idx_users_phone');
            });
        }
    }
};
