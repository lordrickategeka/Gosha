<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('job_cards')) {
            return;
        }

        // Drop existing foreign key if present
        try {
            DB::statement('ALTER TABLE `job_cards` DROP FOREIGN KEY `job_cards_vehicle_id_foreign`');
        } catch (\Throwable $e) {
            // ignore if it doesn't exist
        }

        // Make the vehicle_id column nullable
        try {
            DB::statement('ALTER TABLE `job_cards` MODIFY `vehicle_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // If MODIFY fails (missing column, etc.) ignore
        }

        // Recreate the foreign key with ON DELETE SET NULL to avoid FK errors
        try {
            DB::statement('ALTER TABLE `job_cards` ADD CONSTRAINT `job_cards_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('job_cards')) {
            return;
        }

        // Drop the FK we added
        try {
            DB::statement('ALTER TABLE `job_cards` DROP FOREIGN KEY `job_cards_vehicle_id_foreign`');
        } catch (\Throwable $e) {
            // ignore
        }

        // Make the vehicle_id column NOT NULL again
        try {
            DB::statement('ALTER TABLE `job_cards` MODIFY `vehicle_id` BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            // ignore
        }

        // Recreate the original foreign key without ON DELETE SET NULL (restrict)
        try {
            DB::statement('ALTER TABLE `job_cards` ADD CONSTRAINT `job_cards_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`)');
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
