<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(BasicEntitiesSeeder::class);
        // $this->call(UserSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(DemoDataSeeder::class);
        $this->call(QualityCheckTemplateSeeder::class);
        $this->call(InventoryCategorySeeder::class);
        $this->call(PlatformSettingsSeeder::class);
        $this->call(PricingPlanSeeder::class);

    }
}
