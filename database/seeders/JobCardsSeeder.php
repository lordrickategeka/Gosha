<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobCard;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\ClientNarration;
use Illuminate\Support\Facades\DB;

class JobCardsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Ensure there's at least one user (staff) and one vehicle type
            $user = User::first();
            if (! $user) {
                $user = User::factory()->create([
                    'name' => 'Seeder User',
                    'email' => 'seeder@example.com',
                    'password' => bcrypt('password'),
                ]);
            }

            $vehicleType = VehicleType::first();
            if (! $vehicleType) {
                $vehicleType = VehicleType::create(['name' => 'Car', 'base_price' => 10000, 'description' => 'Default']);
            }

            // Create three customers and their vehicles + job cards
            for ($i = 1; $i <= 3; $i++) {
                $customer = Customer::create([
                    'customer_name' => "Mukasa John Customer {$i}",
                    'phone' => '0700' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'email' => "customer{$i}@example.com",
                    'contact_person' => 'N/A',
                    'address' => '',
                    'nature_of_customer' => 'new',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'vehicle_type_id' => $vehicleType->id,
                    'vehicle_name' => "Vehicle {$i}",
                    'number_plate' => "TEST-00{$i}",
                    'chasis_number' => 'CHASIS' . rand(1000,9999),
                ]);

                $jobCard = JobCard::create([
                    'staff_id' => $user->id,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'vehicle_type_id' => $vehicleType->id,
                    'status' => 'pending',
                    'notes' => "Seeder job card {$i}",
                ]);

                // Create client narrations for this job card
                ClientNarration::create([
                    'job_card_id' => $jobCard->id,
                    'issue' => "Customer reports noise from engine - job card {$i}",
                ]);

                ClientNarration::create([
                    'job_card_id' => $jobCard->id,
                    'issue' => "Also check brakes and change oil - job card {$i}",
                ]);
            }
        });
    }
}
