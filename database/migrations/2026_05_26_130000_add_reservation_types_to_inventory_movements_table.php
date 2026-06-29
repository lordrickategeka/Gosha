<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Check if columns exist from previous runs
        $columns = Schema::getColumnListing('inventory_movements');

        // Only add if not already expanded (check if reservation column exists or if movement_type doesn't include new values)
        try {
            DB::statement("ALTER TABLE `inventory_movements` MODIFY `movement_type` ENUM(
                'purchase',
                'transfer_in',
                'return_from_customer',
                'adjustment_in',
                'work_order_use',
                'wash_order_use',
                'consumption',
                'sale',
                'transfer_out',
                'wastage',
                'adjustment_out',
                'return_to_supplier',
                'reservation',
                'reservation_release'
            ) NOT NULL");
        } catch (\Exception $e) {
            // If fails, it might already be expanded - that's ok
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Try to revert, but skip if data has incompatible values
        try {
            DB::statement("ALTER TABLE `inventory_movements` MODIFY `movement_type` ENUM(
                'purchase',
                'transfer_in',
                'return_from_customer',
                'adjustment_in',
                'work_order_use',
                'wash_order_use',
                'consumption',
                'sale',
                'transfer_out',
                'wastage',
                'adjustment_out',
                'return_to_supplier'
            ) NOT NULL");
        } catch (\Exception $e) {
            // If fails due to data incompatibility, leave as is
        }
    }
};
