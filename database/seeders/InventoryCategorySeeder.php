<?php

namespace Database\Seeders;

use App\Domains\Inventory\Models\InventoryCategory;
use App\Domains\Platform\Models\Vendor;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all vendors to seed categories for each
        $vendors = Vendor::all();

        foreach ($vendors as $vendor) {
            $this->seedServicePartsCategories($vendor);
            $this->seedWashSuppliesCategories($vendor);
        }
    }

    /**
     * Seed service parts categories
     */
    protected function seedServicePartsCategories(Vendor $vendor): void
    {
        $servicePartsCategories = [
            [
                'name' => 'Engine Parts',
                'code' => 'ENG',
                'description' => 'Engine components and related parts',
                'children' => [
                    ['name' => 'Oil Filters', 'code' => 'ENG-OIL'],
                    ['name' => 'Air Filters', 'code' => 'ENG-AIR'],
                    ['name' => 'Fuel Filters', 'code' => 'ENG-FUEL'],
                    ['name' => 'Spark Plugs', 'code' => 'ENG-SPK'],
                    ['name' => 'Ignition Coils', 'code' => 'ENG-IGN'],
                    ['name' => 'Timing Belts & Chains', 'code' => 'ENG-TIM'],
                    ['name' => 'Water Pumps', 'code' => 'ENG-WP'],
                    ['name' => 'Thermostats', 'code' => 'ENG-THERM'],
                    ['name' => 'Gaskets & Seals', 'code' => 'ENG-SEAL'],
                    ['name' => 'Engine Mounts', 'code' => 'ENG-MNT'],
                ],
            ],
            [
                'name' => 'Brake System',
                'code' => 'BRK',
                'description' => 'Brake components and hydraulic parts',
                'children' => [
                    ['name' => 'Brake Pads', 'code' => 'BRK-PAD'],
                    ['name' => 'Brake Discs', 'code' => 'BRK-DISC'],
                    ['name' => 'Brake Drums', 'code' => 'BRK-DRUM'],
                    ['name' => 'Brake Shoes', 'code' => 'BRK-SHOE'],
                    ['name' => 'Brake Calipers', 'code' => 'BRK-CAL'],
                    ['name' => 'Master Cylinders', 'code' => 'BRK-MC'],
                    ['name' => 'Wheel Cylinders', 'code' => 'BRK-WC'],
                    ['name' => 'Brake Hoses', 'code' => 'BRK-HOSE'],
                    ['name' => 'Brake Fluid', 'code' => 'BRK-FLD'],
                ],
            ],
            [
                'name' => 'Suspension & Steering',
                'code' => 'SUSP',
                'description' => 'Suspension and steering components',
                'children' => [
                    ['name' => 'Shock Absorbers', 'code' => 'SUSP-SHK'],
                    ['name' => 'Struts', 'code' => 'SUSP-STR'],
                    ['name' => 'Control Arms', 'code' => 'SUSP-CA'],
                    ['name' => 'Ball Joints', 'code' => 'SUSP-BJ'],
                    ['name' => 'Tie Rod Ends', 'code' => 'SUSP-TRE'],
                    ['name' => 'Stabilizer Links', 'code' => 'SUSP-SL'],
                    ['name' => 'Bushings', 'code' => 'SUSP-BUSH'],
                    ['name' => 'Steering Racks', 'code' => 'SUSP-RACK'],
                    ['name' => 'Power Steering Pumps', 'code' => 'SUSP-PSP'],
                ],
            ],
            [
                'name' => 'Electrical System',
                'code' => 'ELEC',
                'description' => 'Electrical components and accessories',
                'children' => [
                    ['name' => 'Batteries', 'code' => 'ELEC-BAT'],
                    ['name' => 'Alternators', 'code' => 'ELEC-ALT'],
                    ['name' => 'Starters', 'code' => 'ELEC-STR'],
                    ['name' => 'Headlights', 'code' => 'ELEC-HL'],
                    ['name' => 'Tail Lights', 'code' => 'ELEC-TL'],
                    ['name' => 'Bulbs', 'code' => 'ELEC-BLB'],
                    ['name' => 'Fuses & Relays', 'code' => 'ELEC-FUSE'],
                    ['name' => 'Wiring Harnesses', 'code' => 'ELEC-WIRE'],
                    ['name' => 'Sensors', 'code' => 'ELEC-SENS'],
                ],
            ],
            [
                'name' => 'Transmission & Drivetrain',
                'code' => 'TRANS',
                'description' => 'Transmission and drivetrain components',
                'children' => [
                    ['name' => 'Clutch Kits', 'code' => 'TRANS-CLU'],
                    ['name' => 'CV Joints', 'code' => 'TRANS-CV'],
                    ['name' => 'Drive Shafts', 'code' => 'TRANS-DS'],
                    ['name' => 'Transmission Filters', 'code' => 'TRANS-FLT'],
                    ['name' => 'Transmission Fluid', 'code' => 'TRANS-FLD'],
                    ['name' => 'Differential Oil', 'code' => 'TRANS-DIFF'],
                    ['name' => 'Wheel Bearings', 'code' => 'TRANS-WB'],
                ],
            ],
            [
                'name' => 'Cooling System',
                'code' => 'COOL',
                'description' => 'Cooling system components',
                'children' => [
                    ['name' => 'Radiators', 'code' => 'COOL-RAD'],
                    ['name' => 'Radiator Hoses', 'code' => 'COOL-HOSE'],
                    ['name' => 'Coolant', 'code' => 'COOL-FLD'],
                    ['name' => 'Radiator Caps', 'code' => 'COOL-CAP'],
                    ['name' => 'Cooling Fans', 'code' => 'COOL-FAN'],
                    ['name' => 'Expansion Tanks', 'code' => 'COOL-TANK'],
                ],
            ],
            [
                'name' => 'Exhaust System',
                'code' => 'EXH',
                'description' => 'Exhaust components',
                'children' => [
                    ['name' => 'Mufflers', 'code' => 'EXH-MUF'],
                    ['name' => 'Catalytic Converters', 'code' => 'EXH-CAT'],
                    ['name' => 'Exhaust Pipes', 'code' => 'EXH-PIPE'],
                    ['name' => 'Exhaust Gaskets', 'code' => 'EXH-GSKT'],
                    ['name' => 'Oxygen Sensors', 'code' => 'EXH-O2'],
                ],
            ],
            [
                'name' => 'Body & Trim',
                'code' => 'BODY',
                'description' => 'Body panels and trim components',
                'children' => [
                    ['name' => 'Bumpers', 'code' => 'BODY-BMP'],
                    ['name' => 'Fenders', 'code' => 'BODY-FND'],
                    ['name' => 'Mirrors', 'code' => 'BODY-MIR'],
                    ['name' => 'Door Handles', 'code' => 'BODY-HDL'],
                    ['name' => 'Windshield Wipers', 'code' => 'BODY-WPR'],
                    ['name' => 'Weather Stripping', 'code' => 'BODY-WSTR'],
                ],
            ],
            [
                'name' => 'Fluids & Lubricants',
                'code' => 'FLUID',
                'description' => 'Vehicle fluids and lubricants',
                'children' => [
                    ['name' => 'Engine Oil', 'code' => 'FLUID-ENG'],
                    ['name' => 'Transmission Fluid', 'code' => 'FLUID-TRN'],
                    ['name' => 'Power Steering Fluid', 'code' => 'FLUID-PS'],
                    ['name' => 'Brake Fluid', 'code' => 'FLUID-BRK'],
                    ['name' => 'Coolant/Antifreeze', 'code' => 'FLUID-COOL'],
                    ['name' => 'Windshield Washer Fluid', 'code' => 'FLUID-WWF'],
                    ['name' => 'Grease', 'code' => 'FLUID-GRS'],
                ],
            ],
            [
                'name' => 'Tires & Wheels',
                'code' => 'TIRE',
                'description' => 'Tires, wheels, and related accessories',
                'children' => [
                    ['name' => 'Tires', 'code' => 'TIRE-TIRE'],
                    ['name' => 'Wheel Rims', 'code' => 'TIRE-RIM'],
                    ['name' => 'Valve Stems', 'code' => 'TIRE-VLV'],
                    ['name' => 'Wheel Nuts', 'code' => 'TIRE-NUT'],
                    ['name' => 'Hub Caps', 'code' => 'TIRE-HUB'],
                ],
            ],
        ];

        foreach ($servicePartsCategories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = InventoryCategory::create([
                'vendor_id' => $vendor->id,
                'type' => 'service_parts',
                'is_active' => true,
                ...$categoryData,
            ]);

            foreach ($children as $childData) {
                InventoryCategory::create([
                    'vendor_id' => $vendor->id,
                    'parent_id' => $parent->id,
                    'type' => 'service_parts',
                    'is_active' => true,
                    ...$childData,
                ]);
            }
        }
    }

    /**
     * Seed wash supplies categories
     */
    protected function seedWashSuppliesCategories(Vendor $vendor): void
    {
        $washSuppliesCategories = [
            [
                'name' => 'Chemicals & Detergents',
                'code' => 'CHEM',
                'description' => 'Cleaning chemicals and detergents',
                'children' => [
                    ['name' => 'Car Shampoo', 'code' => 'CHEM-SHMP'],
                    ['name' => 'Wheel Cleaners', 'code' => 'CHEM-WHL'],
                    ['name' => 'Engine Degreasers', 'code' => 'CHEM-DEG'],
                    ['name' => 'Glass Cleaners', 'code' => 'CHEM-GLS'],
                    ['name' => 'Interior Cleaners', 'code' => 'CHEM-INT'],
                    ['name' => 'Tire Shine', 'code' => 'CHEM-TIRE'],
                    ['name' => 'Dashboard Polish', 'code' => 'CHEM-DASH'],
                    ['name' => 'All-Purpose Cleaners', 'code' => 'CHEM-APC'],
                    ['name' => 'Bug & Tar Remover', 'code' => 'CHEM-BUG'],
                ],
            ],
            [
                'name' => 'Waxes & Sealants',
                'code' => 'WAX',
                'description' => 'Waxes, sealants, and protective coatings',
                'children' => [
                    ['name' => 'Car Wax (Paste)', 'code' => 'WAX-PASTE'],
                    ['name' => 'Liquid Wax', 'code' => 'WAX-LIQ'],
                    ['name' => 'Spray Wax', 'code' => 'WAX-SPRY'],
                    ['name' => 'Ceramic Coatings', 'code' => 'WAX-CER'],
                    ['name' => 'Paint Sealants', 'code' => 'WAX-SEAL'],
                    ['name' => 'Quick Detailers', 'code' => 'WAX-QD'],
                ],
            ],
            [
                'name' => 'Polishes & Compounds',
                'code' => 'POL',
                'description' => 'Polishing compounds and scratch removers',
                'children' => [
                    ['name' => 'Cutting Compound', 'code' => 'POL-CUT'],
                    ['name' => 'Polishing Compound', 'code' => 'POL-POL'],
                    ['name' => 'Finishing Polish', 'code' => 'POL-FIN'],
                    ['name' => 'Scratch Remover', 'code' => 'POL-SCR'],
                    ['name' => 'Rubbing Compound', 'code' => 'POL-RUB'],
                ],
            ],
            [
                'name' => 'Towels & Cloths',
                'code' => 'TWL',
                'description' => 'Microfiber towels and cleaning cloths',
                'children' => [
                    ['name' => 'Microfiber Towels', 'code' => 'TWL-MF'],
                    ['name' => 'Drying Towels', 'code' => 'TWL-DRY'],
                    ['name' => 'Glass Towels', 'code' => 'TWL-GLS'],
                    ['name' => 'Applicator Pads', 'code' => 'TWL-APP'],
                    ['name' => 'Buffing Cloths', 'code' => 'TWL-BUFF'],
                    ['name' => 'Chamois', 'code' => 'TWL-CHAM'],
                ],
            ],
            [
                'name' => 'Brushes & Sponges',
                'code' => 'BRSH',
                'description' => 'Washing brushes and sponges',
                'children' => [
                    ['name' => 'Wash Mitts', 'code' => 'BRSH-MIT'],
                    ['name' => 'Wheel Brushes', 'code' => 'BRSH-WHL'],
                    ['name' => 'Detail Brushes', 'code' => 'BRSH-DTL'],
                    ['name' => 'Tire Brushes', 'code' => 'BRSH-TIRE'],
                    ['name' => 'Interior Brushes', 'code' => 'BRSH-INT'],
                    ['name' => 'Wash Sponges', 'code' => 'BRSH-SPG'],
                ],
            ],
            [
                'name' => 'Buckets & Containers',
                'code' => 'BUCK',
                'description' => 'Wash buckets and storage containers',
                'children' => [
                    ['name' => 'Wash Buckets', 'code' => 'BUCK-WSH'],
                    ['name' => 'Grit Guards', 'code' => 'BUCK-GRT'],
                    ['name' => 'Spray Bottles', 'code' => 'BUCK-SPRY'],
                    ['name' => 'Chemical Containers', 'code' => 'BUCK-CHEM'],
                ],
            ],
            [
                'name' => 'Vacuum & Air Tools',
                'code' => 'VAC',
                'description' => 'Vacuum and compressed air equipment',
                'children' => [
                    ['name' => 'Vacuum Cleaners', 'code' => 'VAC-VAC'],
                    ['name' => 'Vacuum Attachments', 'code' => 'VAC-ATT'],
                    ['name' => 'Air Blowers', 'code' => 'VAC-AIR'],
                    ['name' => 'Vacuum Filters', 'code' => 'VAC-FLT'],
                ],
            ],
            [
                'name' => 'Pads & Accessories',
                'code' => 'PAD',
                'description' => 'Polishing pads and accessories',
                'children' => [
                    ['name' => 'Foam Pads', 'code' => 'PAD-FOAM'],
                    ['name' => 'Wool Pads', 'code' => 'PAD-WOOL'],
                    ['name' => 'Microfiber Pads', 'code' => 'PAD-MF'],
                    ['name' => 'Backing Plates', 'code' => 'PAD-BACK'],
                ],
            ],
            [
                'name' => 'Interior Care',
                'code' => 'INT',
                'description' => 'Interior detailing products',
                'children' => [
                    ['name' => 'Leather Cleaners', 'code' => 'INT-LETH'],
                    ['name' => 'Leather Conditioners', 'code' => 'INT-COND'],
                    ['name' => 'Fabric Cleaners', 'code' => 'INT-FAB'],
                    ['name' => 'Carpet Cleaners', 'code' => 'INT-CARP'],
                    ['name' => 'Upholstery Protectors', 'code' => 'INT-PROT'],
                    ['name' => 'Odor Eliminators', 'code' => 'INT-ODOR'],
                    ['name' => 'Air Fresheners', 'code' => 'INT-FRSH'],
                ],
            ],
            [
                'name' => 'Protective Equipment',
                'code' => 'PPE',
                'description' => 'Personal protective equipment',
                'children' => [
                    ['name' => 'Gloves', 'code' => 'PPE-GLV'],
                    ['name' => 'Aprons', 'code' => 'PPE-APR'],
                    ['name' => 'Safety Glasses', 'code' => 'PPE-GLS'],
                    ['name' => 'Face Masks', 'code' => 'PPE-MASK'],
                ],
            ],
        ];

        foreach ($washSuppliesCategories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = InventoryCategory::create([
                'vendor_id' => $vendor->id,
                'type' => 'wash_supplies',
                'is_active' => true,
                ...$categoryData,
            ]);

            foreach ($children as $childData) {
                InventoryCategory::create([
                    'vendor_id' => $vendor->id,
                    'parent_id' => $parent->id,
                    'type' => 'wash_supplies',
                    'is_active' => true,
                    ...$childData,
                ]);
            }
        }
    }
}
