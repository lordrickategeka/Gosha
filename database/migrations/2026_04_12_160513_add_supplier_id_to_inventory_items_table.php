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
        if (Schema::hasColumn('inventory_items', 'supplier_id')) {
            return;
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('category_id')->constrained('suppliers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('inventory_items', 'supplier_id')) {
            return;
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            try {
                $table->dropForeign(['supplier_id']);
            } catch (\Throwable $e) {
                // no-op when foreign key is absent
            }

            $table->dropColumn('supplier_id');
        });
    }
};
