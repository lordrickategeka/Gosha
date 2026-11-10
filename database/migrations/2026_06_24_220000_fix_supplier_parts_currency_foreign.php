<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $existingForeignKey = collect(Schema::getForeignKeys($table))
                ->contains(fn ($foreign) => $foreign['columns'] === ['currency_id'] && $foreign['foreign_table'] === 'currencies');

            if (! $existingForeignKey) {
                Schema::table($table, function (Blueprint $blueprint) use ($constraint) {
                    $blueprint->foreign('currency_id', $constraint)->references('id')->on('currencies')->nullOnDelete();
                });
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

            $exists = collect(Schema::getForeignKeys($table))
                ->contains(fn ($foreign) => $foreign['columns'] === ['currency_id'] && $foreign['foreign_table'] === 'currencies');

            if ($exists) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign(['currency_id']));
            }
        }
    }
};
