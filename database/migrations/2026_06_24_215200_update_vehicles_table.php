<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::getColumnListing('vehicles');

        // Step 1: Convert fuel_type from enum to string to allow updating incompatible values (only if not already done)
        if (!in_array('fuel_type_temp', $schema) && in_array('fuel_type', $schema) && !in_array('engine_code', $schema)) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('fuel_type_temp')->nullable()->after('fuel_type');
            });

            // Step 2: Map and copy old values to temp column
            DB::statement("UPDATE vehicles SET fuel_type_temp = 'gasoline' WHERE fuel_type = 'petrol'");
            DB::statement("UPDATE vehicles SET fuel_type_temp = 'bev' WHERE fuel_type = 'electric'");
            DB::statement("UPDATE vehicles SET fuel_type_temp = 'hev' WHERE fuel_type = 'hybrid'");
            DB::statement("UPDATE vehicles SET fuel_type_temp = fuel_type WHERE fuel_type_temp IS NULL");

            // Step 3: Drop old enum column and rename temp
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('fuel_type');
                $table->renameColumn('fuel_type_temp', 'fuel_type');
            });
        }

        Schema::table('vehicles', function (Blueprint $table) use ($schema) {
            // Digital Twin - Additional Engine & Transmission (only if not exists)
            if (!in_array('engine_code', $schema)) {
                $table->string('engine_code')->nullable()->after('engine_number');
            }
            if (!in_array('engine_displacement', $schema)) {
                $table->decimal('engine_displacement', 4, 1)->nullable()->after('engine_code');
            }
            if (!in_array('drivetrain_type', $schema)) {
                $table->enum('drivetrain_type', ['fwd', 'rwd', 'awd', '4wd'])->nullable()->after('engine_displacement');
            }
            if (!in_array('transmission_code', $schema)) {
                $table->string('transmission_code')->nullable()->after('transmission');
            }

            // Digital Twin - Fuel Type (expanded options) - only if not already changed
            if (!in_array('engine_code', $schema)) {
                $table->enum('fuel_type', ['gasoline', 'diesel', 'flex_fuel', 'hev', 'phev', 'bev'])->nullable()->change();
            }

            // Digital Twin - Transmission Type (expanded options)
            if (!in_array('transmission_type', $schema)) {
                $table->enum('transmission_type', ['manual', 'automatic', 'cvt', 'dual_clutch'])->nullable()->after('transmission');
            }

            // Financial & Lifecycle Data
            if (!in_array('in_service_date', $schema)) {
                $table->date('in_service_date')->nullable()->after('year');
            }
            if (!in_array('acquisition_date', $schema)) {
                $table->date('acquisition_date')->nullable()->after('in_service_date');
            }
            if (!in_array('acquisition_cost', $schema)) {
                $table->decimal('acquisition_cost', 12, 2)->nullable()->after('acquisition_date');
            }
            if (!in_array('ownership_status', $schema)) {
                $table->enum('ownership_status', ['owned', 'leased', 'financed', 'customer_owned'])->nullable()->after('acquisition_cost');
            }
            if (!in_array('lease_end_date', $schema)) {
                $table->date('lease_end_date')->nullable()->after('ownership_status');
            }
            if (!in_array('lease_mileage_limit', $schema)) {
                $table->integer('lease_mileage_limit')->nullable()->after('lease_end_date');
            }
            if (!in_array('current_value', $schema)) {
                $table->decimal('current_value', 12, 2)->nullable()->after('lease_mileage_limit');
            }

            // Vehicle Status
            if (!in_array('status', $schema)) {
                $table->enum('status', ['active', 'in_shop', 'decommissioned', 'sold'])->default('active')->after('current_value');
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::getColumnListing('vehicles');

        // Check if columns exist before dropping
        $columnsToDrop = array_filter([
            in_array('engine_code', $schema) ? 'engine_code' : null,
            in_array('engine_displacement', $schema) ? 'engine_displacement' : null,
            in_array('drivetrain_type', $schema) ? 'drivetrain_type' : null,
            in_array('transmission_code', $schema) ? 'transmission_code' : null,
            in_array('transmission_type', $schema) ? 'transmission_type' : null,
            in_array('in_service_date', $schema) ? 'in_service_date' : null,
            in_array('acquisition_date', $schema) ? 'acquisition_date' : null,
            in_array('acquisition_cost', $schema) ? 'acquisition_cost' : null,
            in_array('ownership_status', $schema) ? 'ownership_status' : null,
            in_array('lease_end_date', $schema) ? 'lease_end_date' : null,
            in_array('lease_mileage_limit', $schema) ? 'lease_mileage_limit' : null,
            in_array('current_value', $schema) ? 'current_value' : null,
            in_array('status', $schema) ? 'status' : null,
        ]);

        if (!empty($columnsToDrop)) {
            Schema::table('vehicles', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        // Restore fuel_type enum - but skip if data has incompatible values (safer to just keep as string)
        if (in_array('fuel_type', $schema)) {
            try {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid', 'other'])->nullable()->change();
                });
            } catch (\Exception $e) {
                // If enum change fails (due to incompatible data), leave as string - no action needed
            }
        }
    }
};
