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
        if (!Schema::hasTable('wash_order_items')) {
            return;
        }

        if (!Schema::hasColumn('wash_order_items', 'wash_order_item_id')) {
            Schema::table('wash_order_items', function (Blueprint $table) {
                $table->foreignId('wash_order_item_id')->nullable()->constrained('wash_order_items')->cascadeOnDelete();
            });
        }

        if (!Schema::hasColumn('wash_order_items', 'path')) {
            Schema::table('wash_order_items', function (Blueprint $table) {
                $table->string('path')->nullable();
            });
        }

        if (!Schema::hasColumn('wash_order_items', 'caption')) {
            Schema::table('wash_order_items', function (Blueprint $table) {
                $table->string('caption')->nullable();
            });
        }

        if (!Schema::hasColumn('wash_order_items', 'quantity_used')) {
            Schema::table('wash_order_items', function (Blueprint $table) {
                $table->decimal('quantity_used', 8, 2)->nullable()
                    ->comment('Actual quantity consumed (e.g., 0.5 liters)');
            });
        }

        if (!Schema::hasColumn('wash_order_items', 'inventory_consumed')) {
            Schema::table('wash_order_items', function (Blueprint $table) {
                $table->boolean('inventory_consumed')->default(false);
            });
        }

        if (!Schema::hasColumn('wash_order_items', 'consumed_at')) {
            Schema::table('wash_order_items', function (Blueprint $table) {
                $table->timestamp('consumed_at')->nullable();
            });
        }

        Schema::table('wash_order_items', function (Blueprint $table) {
            $table->index(['inventory_item_id', 'inventory_consumed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank to avoid dropping pre-existing base table.
    }
};
