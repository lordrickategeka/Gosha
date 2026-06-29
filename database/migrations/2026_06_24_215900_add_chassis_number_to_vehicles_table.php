<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing('vehicles');

        Schema::table('vehicles', function (Blueprint $table) use ($columns) {
            // Missing column that is in the model but not in the database
            if (!in_array('chassis_number', $columns)) {
                $table->string('chassis_number')->nullable()->after('vin');
            }

            // Add vendor_id for multi-tenant support (only if not exists)
            // Use nullable to avoid FK constraint issues with existing records
            if (!in_array('vendor_id', $columns)) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('id');
                // Note: FK constraint can be added via separate migration after data is populated
            }
        });

        // If vendor_id was added as nullable, populate it from customers table via the customer relationship
        // This requires matching vendor from each vehicle's customer
        if (!in_array('vendor_id', $columns)) {
            try {
                DB::statement("UPDATE vehicles v
                    INNER JOIN customers c ON v.customer_id = c.id
                    SET v.vendor_id = c.vendor_id
                    WHERE v.vendor_id IS NULL");
            } catch (\Exception $e) {
                // If update fails, leave as null - can be populated later
            }

            // Now add FK constraint (nullable columns can still have FK)
            Schema::table('vehicles', function (Blueprint $table) {
                $table->foreign('vendor_id')
                    ->references('id')
                    ->on('vendors')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'chassis_number']);
        });
    }
};
