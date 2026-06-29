<?php

namespace Database\Seeders;

use App\Domains\ServiceConfig\Models\QualityCheckTemplate;
use Illuminate\Database\Seeder;

class QualityCheckTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // A. EXTERIOR
            'exterior' => [
                'Body condition (dents/scratches)',
                'Headlights & Indicators',
                'Brake & Tail Lights',
                'Side Mirrors',
                'Windshield (cracks/chips)',
                'Wipers & Washers',
                'Number Plates',
                'Paint Condition',
                'Doors & Locks',
                'Fuel Cap',
                'Spare Wheel & Jack',
                'Tyre Tread & Pressure (All)',
            ],

            // B. INTERIOR
            'interior' => [
                'Dashboard Lights',
                'Gauges (Speedometer, Fuel, etc)',
                'Horn',
                'Seat Belts',
                'Seats & Upholstery',
                'Floor Mats',
                'Air Conditioning / Heater',
                'Audio System',
                'Windows (manual/electric)',
                'Interior Lights',
            ],

            // C. ENGINE COMPARTMENT
            'engine_compartment' => [
                'Engine Oil Level & Condition',
                'Coolant Level & Condition',
                'Brake Fluid',
                'Transmission Fluid',
                'Battery & Terminals',
                'Belts & Hoses',
                'Air Filter',
                'Radiator',
                'Leaks (oil, coolant, etc)',
            ],

            // D. UNDERBODY & SUSPENSION
            'underbody_suspension' => [
                'Exhaust System',
                'Suspension Components',
                'Brake Pads & Discs',
                'Steering System',
                'Drive Shafts / CV Joints',
                'Chassis Frame Condition',
            ],

            // E. ROAD TEST (Optional)
            'road_test' => [
                'Engine Performance',
                'Gear Shift Smoothness',
                'Steering Response',
                'Brake Functionality',
                'Suspension/Noise on Road',
                'Unusual Vibrations',
            ],
        ];

        $sortOrder = 0;

        foreach ($templates as $section => $items) {
            foreach ($items as $item) {
                QualityCheckTemplate::create([
                    'vendor_id' => null, // System default
                    'section' => $section,
                    'item_name' => $item,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                    'is_default' => true,
                ]);
            }
        }

        $this->command->info('Quality check templates seeded successfully.');
    }
}
