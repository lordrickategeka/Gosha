<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('currencies')) {
            return;
        }

        foreach ([
            'supplier_parts' => 'supplier_parts_currency_id_foreign',
            'work_order_part_sources' => 'work_order_part_sources_currency_id_foreign',
        ] as $table => $constraint) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'currency_id')) {
                continue;
            }

            // A failed or manually-run migration may already have added this FK even
            // though Laravel has not recorded the migration as complete.
            $existingForeignKey = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', 'currency_id')
                ->where('REFERENCED_TABLE_NAME', 'currencies')
                ->exists();

            if (! $existingForeignKey) {
                DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraint}`
                    FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL");
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'supplier_parts' => 'supplier_parts_currency_id_foreign',
            'work_order_part_sources' => 'work_order_part_sources_currency_id_foreign',
        ] as $table => $constraint) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('CONSTRAINT_NAME', $constraint)
                ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
                ->exists();

            if ($exists) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
            }
        }
    }
};
