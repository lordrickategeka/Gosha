<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
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
    }
};
