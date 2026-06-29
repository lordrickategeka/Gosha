<?php

namespace App\Console\Commands;

use App\Domains\Vehicles\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixFuelTypes extends Command
{
    protected $signature = 'fix:fuel-types {--check : Check current fuel types without fixing}';
    protected $description = 'Fix fuel_type values in vehicles table from petrol to gasoline';

    public function handle(): int
    {
        if ($this->option('check')) {
            $vehicles = Vehicle::all(['id', 'registration_number', 'fuel_type']);
            $this->info("Current vehicles in database:");
            foreach ($vehicles as $v) {
                $this->line("  {$v->registration_number}: {$v->fuel_type}");
            }
            return Command::SUCCESS;
        }

        $updated = DB::table('vehicles')
            ->where('fuel_type', 'petrol')
            ->update(['fuel_type' => 'gasoline']);

        $this->info("Updated {$updated} vehicles from 'petrol' to 'gasoline'");

        return Command::SUCCESS;
    }
}
