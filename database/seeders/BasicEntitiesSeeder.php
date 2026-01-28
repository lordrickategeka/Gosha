<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\ServiceType;
use App\Models\Staff;

class BasicEntitiesSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::factory()->create([
            'name' => 'Admin GOS',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);

        // Vehicle Types
        $vehicleTypes = [
            ['name' => 'Car', 'base_price' => 20000, 'description' => 'Standard car'],
            ['name' => 'Truck', 'base_price' => 40000, 'description' => 'Heavy truck'],
            ['name' => 'Motorcycle', 'base_price' => 10000, 'description' => 'Two-wheeler'],
            ['name' => 'SUV', 'base_price' => 30000, 'description' => 'Sport Utility Vehicle'],
        ];
        foreach ($vehicleTypes as $type) {
            VehicleType::create($type);
        }

        // Service Types
        $serviceTypes = [
            ['name' => 'Oil Change', 'price' => 15000],
            ['name' => 'Brake Repair', 'price' => 25000],
            ['name' => 'Engine Diagnostic', 'price' => 30000],
            ['name' => 'Tire Rotation', 'price' => 10000],
            ['name' => 'AC Repair', 'price' => 20000],
            ['name' => 'Transmission Service', 'price' => 35000],
        ];
        foreach ($serviceTypes as $service) {
            ServiceType::create($service);
        }

        // Staff
        $staff = [
            ['name' => 'John Doe', 'phone' => '0700000001', 'email' => 'john@example.com', 'role' => 'manager', 'base_salary' => 500000, 'is_active' => true],
            ['name' => 'Jane Smith', 'phone' => '0700000002', 'email' => 'jane@example.com', 'role' => 'washer', 'base_salary' => 300000, 'is_active' => true],
            ['name' => 'Mike Brown', 'phone' => '0700000003', 'email' => 'mike@example.com', 'role' => 'attendant', 'base_salary' => 250000, 'is_active' => true],
        ];
        foreach ($staff as $member) {
            Staff::create($member);
        }
    }
}
