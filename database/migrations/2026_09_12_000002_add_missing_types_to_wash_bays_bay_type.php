<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The wash_bays.bay_type column was created with only 4 of the 6 values
     * defined on App\Domains\Operations\Enums\WashBayType (missing 'standard'
     * and 'premium'), even though both the create/edit form and the model's
     * validation rules already offer all 6 — so saving a bay as "Standard"
     * (the form's own default) or "Premium" throws a SQL error. Widen the
     * enum to match what the app already lets a user pick.
     */
    public function up(): void
    {
        // SQLite stores these values as text and has no MODIFY COLUMN syntax.
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }
        DB::statement("ALTER TABLE wash_bays MODIFY COLUMN bay_type ENUM('basic', 'standard', 'premium', 'full_service', 'detailing', 'automated') NOT NULL DEFAULT 'full_service'");
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }
        DB::statement("UPDATE wash_bays SET bay_type = 'full_service' WHERE bay_type IN ('standard', 'premium')");
        DB::statement("ALTER TABLE wash_bays MODIFY COLUMN bay_type ENUM('basic', 'full_service', 'detailing', 'automated') NOT NULL DEFAULT 'full_service'");
    }
};
