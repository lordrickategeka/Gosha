<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('inventory_items', 'branch_id')) {
            return;
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['branch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('inventory_items', 'branch_id')) {
            return;
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            try {
                $table->dropForeign(['branch_id']);
            } catch (\Throwable $e) {
                // no-op when foreign key is absent
            }

            try {
                $table->dropIndex(['branch_id', 'is_active']);
            } catch (\Throwable $e) {
                // no-op when index is absent
            }

            $table->dropColumn('branch_id');
        });
    }
};
