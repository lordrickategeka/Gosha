<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();


            $table->enum('movement_type', [
                // Stock in
                'purchase',          // Purchased from supplier
                'transfer_in',       // Received from another branch
                'return_from_customer', // Customer returned item
                'adjustment_in',     // Manual increase (stock count correction)

                // Stock out
                'work_order_use',    // Consumed in work order
                'wash_order_use',    // Consumed in wash order
                'consumption',       // Generic consumption (work/wash orders)
                'sale',              // Sold directly (retail)
                'transfer_out',      // Sent to another branch
                'wastage',           // Damaged/expired/lost
                'adjustment_out',    // Manual decrease (stock count correction)
                'return_to_supplier' // Returned to supplier
            ]);

            // Quantity (positive for in, negative for out)
            $table->decimal('quantity_change', 10, 2); // Can be negative
            $table->decimal('quantity_after', 10, 2);  // Snapshot after movement

            // Costing

            // Reference tracking (polymorphic)
            $table->nullableMorphs('movable'); // work_order, wash_order, purchase_order, etc.

            // Transfer tracking
            $table->foreignId('from_branch_id')->nullable()->constrained('branches');
            $table->foreignId('to_branch_id')->nullable()->constrained('branches');

            // Supplier tracking
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            // Audit
            $table->text('notes')->nullable();
            $table->timestamp('movement_date')->useCurrent();

            $table->timestamps();

            // Indexes
            $table->index(['inventory_item_id', 'movement_type']);
            $table->index(['branch_id', 'movement_date']);
            $table->index(['inventory_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
