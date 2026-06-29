<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First check if currency_id column exists but currency_id_foreign constraint doesn't
        // This fixes the broken constraint from 2026_05_15_000006 which ran before currencies table existed

        // Check if table exists
        if (!Schema::hasTable('supplier_parts')) {
            return;
        }

        // Check if currencies table exists
        if (!Schema::hasTable('currencies')) {
            return;
        }

        // Check if currency_id column exists
        if (!Schema::hasColumn('supplier_parts', 'currency_id')) {
            return;
        }

// Add foreign key using raw SQL (more reliable than Schema builder for existing columns)
        DB::statement('ALTER TABLE supplier_parts ADD CONSTRAINT supplier_parts_currency_id_foreign
            FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE SET NULL');

        // Also add the currency foreign key to work_order_part_sources table
        if (Schema::hasTable('work_order_part_sources')) {
            DB::statement('ALTER TABLE work_order_part_sources ADD CONSTRAINT work_order_part_sources_currency_id_foreign
                FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE SET NULL');
        }
    }

public function down(): void
    {
        try {
            DB::statement('ALTER TABLE supplier_parts DROP FOREIGN KEY supplier_parts_currency_id_foreign');
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }
        try {
            DB::statement('ALTER TABLE work_order_part_sources DROP FOREIGN KEY work_order_part_sources_currency_id_foreign');
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }
    }
};
