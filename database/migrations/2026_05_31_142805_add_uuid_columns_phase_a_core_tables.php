<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'branches',
            'customers',
            'vehicles',
            'suppliers',
            'inventory_items',
            'work_orders',
            'work_order_items',
            'quotations',
            'quotation_items',
            'invoices',
            'invoice_items',
            'payments',
            'inventory_movements',
            'debit_notes',
            'debit_note_items',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) {
                if (!Schema::hasColumn($tableBlueprint->getTable(), 'uuid')) {
                    $tableBlueprint->uuid('uuid')->nullable()->after('id');
                }
            });

            DB::table($table)
                ->whereNull('uuid')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    }
                });

            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableName = $tableBlueprint->getTable();

                if (Schema::hasColumn($tableName, 'uuid')) {
                    $tableBlueprint->unique('uuid', $tableName . '_uuid_unique');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'branches',
            'customers',
            'vehicles',
            'suppliers',
            'inventory_items',
            'work_orders',
            'work_order_items',
            'quotations',
            'quotation_items',
            'invoices',
            'invoice_items',
            'payments',
            'inventory_movements',
            'debit_notes',
            'debit_note_items',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'uuid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableName = $tableBlueprint->getTable();
                $indexName = $tableName . '_uuid_unique';

                try {
                    $tableBlueprint->dropUnique($indexName);
                } catch (\Throwable $e) {
                    // Ignore if index name differs by driver.
                }

                $tableBlueprint->dropColumn('uuid');
            });
        }
    }
};
