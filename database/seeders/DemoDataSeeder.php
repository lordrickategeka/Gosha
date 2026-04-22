<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceBay;
use App\Models\ServiceTemplate;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\VendorBillingConfig;
use App\Models\VehicleCategory;
use App\Models\WashBay;
use App\Models\WashOrder;
use App\Models\WashPackage;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
         // ========================================
        // DEMO VENDOR: AutoCare Garage
        // ========================================
        $vendor = Vendor::create([
            'name' => 'AutoCare Garage',
            'slug' => 'autocare-garage',
            'email' => 'info@autocaregarage.com',
            'phone' => '+256 700 123456',
            'address' => 'Plot 45, Kampala Road, Kampala',
            'status' => 'active',
        ]);

        // ========================================
        // PLATFORM SUPER ADMIN
        // ========================================
        $superAdmin = User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@garageplatform.com',
            'password' => Hash::make('password'),
            'is_platform_user' => true,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super-admin');


        VendorBillingConfig::create([
            'vendor_id' => $vendor->id,
            'billing_model' => 'subscription',
            'subscription_amount' => 150000,
            'subscription_interval' => 'monthly',
            'next_billing_date' => now()->addMonth(),
        ]);

        // Create default settings
        Setting::createDefaultsForVendor($vendor->id);

        // ========================================
        // BRANCHES
        // ========================================
        $mainBranch = Branch::create([
            'vendor_id' => $vendor->id,
            'name' => 'Main Branch - Kampala',
            'address' => 'Plot 45, Kampala Road, Kampala',
            'phone' => '+256 700 123456',
            'email' => 'kampala@autocaregarage.com',
            'is_active' => true,
            'is_main' => true,
        ]);

        $secondBranch = Branch::create([
            'vendor_id' => $vendor->id,
            'name' => 'Entebbe Branch',
            'address' => 'Plot 12, Entebbe Road, Entebbe',
            'phone' => '+256 700 654321',
            'email' => 'entebbe@autocaregarage.com',
            'is_active' => true,
            'is_main' => false,
        ]);

        // ========================================
        // USERS / STAFF
        // ========================================
        $owner = User::create([
            'vendor_id' => $vendor->id,
            'name' => 'John Mukasa',
            'email' => 'john@autocaregarage.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $owner->assignRole('vendor-owner');
        $owner->branches()->attach([$mainBranch->id, $secondBranch->id], ['is_primary' => true]);

        $manager = User::create([
            'vendor_id' => $vendor->id,
            'name' => 'Sarah Namuli',
            'email' => 'sarah@autocaregarage.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manager->assignRole('branch-manager');
        $manager->branches()->attach($mainBranch->id, ['is_primary' => true]);

        $technician1 = User::create([
            'vendor_id' => $vendor->id,
            'name' => 'Peter Ochieng',
            'email' => 'peter@autocaregarage.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $technician1->assignRole('technician');
        $technician1->branches()->attach($mainBranch->id, ['is_primary' => true]);

        $technician2 = User::create([
            'vendor_id' => $vendor->id,
            'name' => 'James Okello',
            'email' => 'james@autocaregarage.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $technician2->assignRole('technician');
        $technician2->branches()->attach($mainBranch->id, ['is_primary' => true]);

        $washAttendant = User::create([
            'vendor_id' => $vendor->id,
            'name' => 'Grace Auma',
            'email' => 'grace@autocaregarage.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $washAttendant->assignRole('wash-attendant');
        $washAttendant->branches()->attach($mainBranch->id, ['is_primary' => true]);

        $cashier = User::create([
            'vendor_id' => $vendor->id,
            'name' => 'Mary Nakato',
            'email' => 'mary@autocaregarage.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $cashier->assignRole('cashier');
        $cashier->branches()->attach($mainBranch->id, ['is_primary' => true]);

        $storekeeper = User::create([
            'vendor_id' => $vendor->id,
            'name' => 'David Ssemakula',
            'email' => 'david@autocaregarage.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $storekeeper->assignRole('storekeeper');
        $storekeeper->branches()->attach($mainBranch->id, ['is_primary' => true]);

        // ========================================
        // SERVICE BAYS
        // ========================================
        $serviceBays = [
            ['name' => 'Bay 1 - General', 'bay_type' => 'general', 'status' => 'available'],
            ['name' => 'Bay 2 - General', 'bay_type' => 'general', 'status' => 'occupied'],
            ['name' => 'Bay 3 - Diagnostics', 'bay_type' => 'diagnostics', 'status' => 'available'],
            ['name' => 'Bay 4 - Electrical', 'bay_type' => 'electrical', 'status' => 'maintenance'],
            ['name' => 'Bay 5 - AC', 'bay_type' => 'ac', 'status' => 'available'],
        ];

        foreach ($serviceBays as $bay) {
            ServiceBay::create(array_merge($bay, ['branch_id' => $mainBranch->id]));
        }

        // ========================================
        // WASH BAYS
        // ========================================
        $washBays = [
            ['name' => 'Wash Bay 1', 'bay_type' => 'full_service', 'status' => 'available'],
            ['name' => 'Wash Bay 2', 'bay_type' => 'full_service', 'status' => 'occupied'],
            ['name' => 'Detailing Bay', 'bay_type' => 'detailing', 'status' => 'available'],
        ];

        foreach ($washBays as $bay) {
            WashBay::create(array_merge($bay, ['branch_id' => $mainBranch->id]));
        }

        // ========================================
        // INVENTORY CATEGORIES
        // ========================================
        $partsCategory = InventoryCategory::create([
            'vendor_id' => $vendor->id,
            'name' => 'Engine Parts',
            'type' => 'parts',
        ]);

        $oilsCategory = InventoryCategory::create([
            'vendor_id' => $vendor->id,
            'name' => 'Oils & Lubricants',
            'type' => 'consumables',
        ]);

        $filtersCategory = InventoryCategory::create([
            'vendor_id' => $vendor->id,
            'name' => 'Filters',
            'type' => 'parts',
        ]);

        $washSuppliesCategory = InventoryCategory::create([
            'vendor_id' => $vendor->id,
            'name' => 'Wash Supplies',
            'type' => 'wash_supplies',
        ]);

        // ========================================
        // SUPPLIERS
        // ========================================
        $supplier1 = Supplier::create([
            'vendor_id' => $vendor->id,
            'name' => 'AutoParts Uganda Ltd',
            'contact_person' => 'Robert Kizza',
            'email' => 'sales@autopartsug.com',
            'phone' => '+256 700 888888',
            'address' => 'Industrial Area, Kampala',
            'is_active' => true,
        ]);

        $supplier2 = Supplier::create([
            'vendor_id' => $vendor->id,
            'name' => 'Shell Uganda',
            'contact_person' => 'Alice Mbabazi',
            'email' => 'orders@shell.co.ug',
            'phone' => '+256 700 999999',
            'is_active' => true,
        ]);

        // ========================================
        // INVENTORY ITEMS
        // ========================================
        $inventoryItems = [
            // Engine Parts
            ['category_id' => $partsCategory->id, 'name' => 'Spark Plug - NGK', 'sku' => 'SP-NGK-001', 'unit' => 'pieces', 'quantity' => 50, 'reorder_level' => 10, 'cost_price' => 15000, 'selling_price' => 25000],
            ['category_id' => $partsCategory->id, 'name' => 'Brake Pads - Front', 'sku' => 'BP-FRT-001', 'unit' => 'set', 'quantity' => 20, 'reorder_level' => 5, 'cost_price' => 80000, 'selling_price' => 120000],
            ['category_id' => $partsCategory->id, 'name' => 'Brake Pads - Rear', 'sku' => 'BP-RR-001', 'unit' => 'set', 'quantity' => 15, 'reorder_level' => 5, 'cost_price' => 70000, 'selling_price' => 100000],
            ['category_id' => $partsCategory->id, 'name' => 'Battery - 12V 60Ah', 'sku' => 'BAT-12V-60', 'unit' => 'pieces', 'quantity' => 8, 'reorder_level' => 3, 'cost_price' => 250000, 'selling_price' => 350000],
            // Oils
            ['category_id' => $oilsCategory->id, 'name' => 'Engine Oil 5W-30 (5L)', 'sku' => 'OIL-5W30-5L', 'unit' => 'pieces', 'quantity' => 30, 'reorder_level' => 10, 'cost_price' => 120000, 'selling_price' => 160000],
            ['category_id' => $oilsCategory->id, 'name' => 'Engine Oil 10W-40 (5L)', 'sku' => 'OIL-10W40-5L', 'unit' => 'pieces', 'quantity' => 25, 'reorder_level' => 10, 'cost_price' => 100000, 'selling_price' => 140000],
            ['category_id' => $oilsCategory->id, 'name' => 'Brake Fluid DOT4 (1L)', 'sku' => 'BRK-DOT4-1L', 'unit' => 'pieces', 'quantity' => 20, 'reorder_level' => 5, 'cost_price' => 25000, 'selling_price' => 40000],
            // Filters
            ['category_id' => $filtersCategory->id, 'name' => 'Oil Filter - Toyota', 'sku' => 'FLT-OIL-TOY', 'unit' => 'pieces', 'quantity' => 40, 'reorder_level' => 10, 'cost_price' => 20000, 'selling_price' => 35000],
            ['category_id' => $filtersCategory->id, 'name' => 'Air Filter - Universal', 'sku' => 'FLT-AIR-UNI', 'unit' => 'pieces', 'quantity' => 35, 'reorder_level' => 10, 'cost_price' => 30000, 'selling_price' => 50000],
            ['category_id' => $filtersCategory->id, 'name' => 'Fuel Filter', 'sku' => 'FLT-FUEL-001', 'unit' => 'pieces', 'quantity' => 25, 'reorder_level' => 8, 'cost_price' => 25000, 'selling_price' => 45000],
            // Wash Supplies
            ['category_id' => $washSuppliesCategory->id, 'name' => 'Car Shampoo (20L)', 'sku' => 'WSH-SHMP-20L', 'unit' => 'pieces', 'quantity' => 10, 'reorder_level' => 3, 'cost_price' => 80000, 'selling_price' => 0],
            ['category_id' => $washSuppliesCategory->id, 'name' => 'Tire Shine (5L)', 'sku' => 'WSH-TIRE-5L', 'unit' => 'pieces', 'quantity' => 8, 'reorder_level' => 2, 'cost_price' => 50000, 'selling_price' => 0],
            ['category_id' => $washSuppliesCategory->id, 'name' => 'Interior Cleaner (5L)', 'sku' => 'WSH-INT-5L', 'unit' => 'pieces', 'quantity' => 6, 'reorder_level' => 2, 'cost_price' => 60000, 'selling_price' => 0],
            ['category_id' => $washSuppliesCategory->id, 'name' => 'Wax Polish (1L)', 'sku' => 'WSH-WAX-1L', 'unit' => 'pieces', 'quantity' => 12, 'reorder_level' => 3, 'cost_price' => 45000, 'selling_price' => 0],
        ];

        foreach ($inventoryItems as $item) {
            InventoryItem::create(array_merge($item, ['vendor_id' => $vendor->id, 'is_active' => true]));
        }

        // ========================================
        // CUSTOMERS
        // ========================================
        $customers = [
            ['name' => 'Robert Kayongo', 'phone' => '+256 772 111111', 'email' => 'robert.kayongo@email.com', 'customer_type' => 'individual', 'loyalty_points' => 150],
            ['name' => 'Mary Namutebi', 'phone' => '+256 772 222222', 'email' => 'mary.namutebi@email.com', 'customer_type' => 'individual', 'loyalty_points' => 80],
            ['name' => 'Samuel Okot', 'phone' => '+256 772 333333', 'email' => 'samuel.okot@email.com', 'customer_type' => 'individual', 'loyalty_points' => 200],
            ['name' => 'ABC Logistics Ltd', 'phone' => '+256 772 444444', 'email' => 'fleet@abclogistics.co.ug', 'customer_type' => 'corporate', 'company_name' => 'ABC Logistics Ltd', 'credit_limit' => 5000000, 'loyalty_points' => 500],
            ['name' => 'Uganda Tours & Travel', 'phone' => '+256 772 555555', 'email' => 'transport@ugatours.com', 'customer_type' => 'corporate', 'company_name' => 'Uganda Tours & Travel', 'credit_limit' => 3000000, 'loyalty_points' => 350],
            ['name' => 'Grace Akello', 'phone' => '+256 772 666666', 'customer_type' => 'individual', 'loyalty_points' => 50],
            ['name' => 'Patrick Mugisha', 'phone' => '+256 772 777777', 'email' => 'pmugisha@gmail.com', 'customer_type' => 'individual', 'loyalty_points' => 120],
            ['name' => 'Diana Nassanga', 'phone' => '+256 772 888888', 'customer_type' => 'individual', 'loyalty_points' => 0],
        ];

        $createdCustomers = [];
        foreach ($customers as $customerData) {
            $createdCustomers[] = Customer::create(array_merge($customerData, ['vendor_id' => $vendor->id]));
        }

        // ========================================
        // VEHICLES
        // ========================================
        $vehicles = [
            ['customer_id' => $createdCustomers[0]->id, 'registration_number' => 'UAA 123A', 'make' => 'Toyota', 'model' => 'Corolla', 'year' => 2019, 'color' => 'Silver', 'fuel_type' => 'petrol', 'mileage' => 45000],
            ['customer_id' => $createdCustomers[0]->id, 'registration_number' => 'UAB 456B', 'make' => 'Toyota', 'model' => 'Hilux', 'year' => 2020, 'color' => 'White', 'fuel_type' => 'diesel', 'mileage' => 32000],
            ['customer_id' => $createdCustomers[1]->id, 'registration_number' => 'UAC 789C', 'make' => 'Honda', 'model' => 'CR-V', 'year' => 2018, 'color' => 'Black', 'fuel_type' => 'petrol', 'mileage' => 58000],
            ['customer_id' => $createdCustomers[2]->id, 'registration_number' => 'UAD 012D', 'make' => 'Nissan', 'model' => 'X-Trail', 'year' => 2021, 'color' => 'Blue', 'fuel_type' => 'petrol', 'mileage' => 25000],
            ['customer_id' => $createdCustomers[3]->id, 'registration_number' => 'UAE 345E', 'make' => 'Isuzu', 'model' => 'FRR', 'year' => 2017, 'color' => 'White', 'fuel_type' => 'diesel', 'mileage' => 120000],
            ['customer_id' => $createdCustomers[3]->id, 'registration_number' => 'UAF 678F', 'make' => 'Isuzu', 'model' => 'FRR', 'year' => 2018, 'color' => 'White', 'fuel_type' => 'diesel', 'mileage' => 95000],
            ['customer_id' => $createdCustomers[4]->id, 'registration_number' => 'UAG 901G', 'make' => 'Toyota', 'model' => 'Hiace', 'year' => 2019, 'color' => 'Silver', 'fuel_type' => 'diesel', 'mileage' => 78000],
            ['customer_id' => $createdCustomers[5]->id, 'registration_number' => 'UAH 234H', 'make' => 'Suzuki', 'model' => 'Swift', 'year' => 2020, 'color' => 'Red', 'fuel_type' => 'petrol', 'mileage' => 28000],
            ['customer_id' => $createdCustomers[6]->id, 'registration_number' => 'UAI 567I', 'make' => 'Subaru', 'model' => 'Forester', 'year' => 2017, 'color' => 'Green', 'fuel_type' => 'petrol', 'mileage' => 72000],
            ['customer_id' => $createdCustomers[7]->id, 'registration_number' => 'UAJ 890J', 'make' => 'Mercedes', 'model' => 'C200', 'year' => 2016, 'color' => 'Black', 'fuel_type' => 'petrol', 'mileage' => 85000],
        ];

        $createdVehicles = [];
        foreach ($vehicles as $vehicleData) {
            $createdVehicles[] = Vehicle::create($vehicleData);
        }

        // ========================================
        // VEHICLE CATEGORIES (for wash pricing)
        // ========================================
        $vehicleCategories = [
            ['name' => 'Sedan/Hatchback', 'price_multiplier' => 1.00, 'sort_order' => 1],
            ['name' => 'SUV/Crossover', 'price_multiplier' => 1.25, 'sort_order' => 2],
            ['name' => 'Pickup/Truck', 'price_multiplier' => 1.50, 'sort_order' => 3],
            ['name' => 'Van/Minibus', 'price_multiplier' => 1.75, 'sort_order' => 4],
            ['name' => 'Bus/Large Vehicle', 'price_multiplier' => 2.00, 'sort_order' => 5],
        ];

        foreach ($vehicleCategories as $cat) {
            VehicleCategory::create(array_merge($cat, ['vendor_id' => $vendor->id]));
        }

        // ========================================
        // WASH PACKAGES
        // ========================================
        $washPackages = [
            ['name' => 'Basic Wash', 'wash_type' => 'basic', 'description' => 'Exterior wash only', 'includes' => ['Exterior wash', 'Rinse', 'Dry'], 'estimated_duration_minutes' => 15, 'price' => 15000, 'sort_order' => 1],
            ['name' => 'Standard Wash', 'wash_type' => 'standard', 'description' => 'Exterior + basic interior', 'includes' => ['Exterior wash', 'Interior vacuum', 'Dashboard wipe', 'Window cleaning'], 'estimated_duration_minutes' => 30, 'price' => 25000, 'sort_order' => 2],
            ['name' => 'Premium Wash', 'wash_type' => 'premium', 'description' => 'Full service wash', 'includes' => ['Exterior wash', 'Full interior cleaning', 'Dashboard polish', 'Tire shine', 'Air freshener'], 'estimated_duration_minutes' => 45, 'price' => 40000, 'sort_order' => 3],
            ['name' => 'Full Detail', 'wash_type' => 'full_detail', 'description' => 'Complete detailing service', 'includes' => ['Hand wash', 'Clay bar treatment', 'Polish', 'Wax', 'Full interior detail', 'Engine bay cleaning'], 'estimated_duration_minutes' => 120, 'price' => 150000, 'sort_order' => 4],
            ['name' => 'Engine Wash', 'wash_type' => 'engine', 'description' => 'Engine bay cleaning', 'includes' => ['Engine degrease', 'Pressure wash', 'Dressing'], 'estimated_duration_minutes' => 30, 'price' => 35000, 'sort_order' => 5],
        ];

        foreach ($washPackages as $pkg) {
            WashPackage::create(array_merge($pkg, ['vendor_id' => $vendor->id, 'is_active' => true]));
        }

        // ========================================
        // SERVICE TEMPLATES
        // ========================================
        $serviceTemplates = [
            ['name' => 'Basic Service', 'description' => 'Oil change and basic inspection', 'category' => 'service', 'estimated_duration_minutes' => 60, 'base_price' => 150000],
            ['name' => 'Full Service', 'description' => 'Complete vehicle service', 'category' => 'service', 'estimated_duration_minutes' => 180, 'base_price' => 350000],
            ['name' => 'Brake Service', 'description' => 'Brake inspection and pad replacement', 'category' => 'repair', 'estimated_duration_minutes' => 120, 'base_price' => 200000],
            ['name' => 'AC Service', 'description' => 'Air conditioning check and regas', 'category' => 'ac', 'estimated_duration_minutes' => 90, 'base_price' => 180000],
            ['name' => 'Diagnostic Check', 'description' => 'Computer diagnostic scan', 'category' => 'diagnostics', 'estimated_duration_minutes' => 30, 'base_price' => 50000],
        ];

        foreach ($serviceTemplates as $template) {
            ServiceTemplate::create(array_merge($template, ['vendor_id' => $vendor->id, 'is_active' => true]));
        }

        // ========================================
        // EXPENSE CATEGORIES
        // ========================================
        $expenseCategories = [
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet'],
            ['name' => 'Rent', 'description' => 'Office and workshop rent'],
            ['name' => 'Salaries', 'description' => 'Staff salaries and wages'],
            ['name' => 'Equipment', 'description' => 'Tools and equipment purchases'],
            ['name' => 'Supplies', 'description' => 'Consumables and supplies'],
            ['name' => 'Transport', 'description' => 'Fuel and transport costs'],
            ['name' => 'Marketing', 'description' => 'Advertising and promotion'],
            ['name' => 'Miscellaneous', 'description' => 'Other expenses'],
        ];

        foreach ($expenseCategories as $cat) {
            ExpenseCategory::create(array_merge($cat, ['vendor_id' => $vendor->id, 'is_active' => true]));
        }

        // ========================================
        // COMMISSION RULES
        // ========================================
        CommissionRule::create([
            'vendor_id' => $vendor->id,
            'name' => 'Technician Labor Commission',
            'role' => 'technician',
            'type' => 'percentage',
            'value' => 10,
            'applies_to' => 'labor',
            'is_active' => true,
            'description' => '10% of labor charges',
        ]);

        CommissionRule::create([
            'vendor_id' => $vendor->id,
            'name' => 'Wash Attendant Commission',
            'role' => 'wash-attendant',
            'type' => 'percentage',
            'value' => 15,
            'applies_to' => 'wash',
            'is_active' => true,
            'description' => '15% of wash service value',
        ]);

        // ========================================
        // SAMPLE WORK ORDERS
        // ========================================
        $serviceBay = ServiceBay::where('branch_id', $mainBranch->id)->where('status', 'occupied')->first();

        // Work order in progress
        $workOrder1 = WorkOrder::create([
            'branch_id' => $mainBranch->id,
            'vehicle_id' => $createdVehicles[0]->id,
            'customer_id' => $createdCustomers[0]->id,
            'service_bay_id' => $serviceBay?->id,
            'assigned_technician_id' => $technician1->id,
            'created_by' => $manager->id,
            'type' => 'service',
            'status' => 'in_progress',
            'is_combo' => true,
            'priority' => 'normal',
            'mileage_in' => 45230,
            'customer_notes' => 'Due for regular service, also check brakes',
            'checked_in_at' => now()->subHours(2),
            'started_at' => now()->subHour(),
        ]);

        $workOrder1->items()->createMany([
            ['item_type' => 'labor', 'description' => 'Full Service Labor', 'quantity' => 1, 'unit_price' => 150000],
            ['item_type' => 'part', 'description' => 'Engine Oil 5W-30 (5L)', 'inventory_item_id' => InventoryItem::where('sku', 'OIL-5W30-5L')->first()->id, 'quantity' => 1, 'unit_price' => 160000],
            ['item_type' => 'part', 'description' => 'Oil Filter', 'inventory_item_id' => InventoryItem::where('sku', 'FLT-OIL-TOY')->first()->id, 'quantity' => 1, 'unit_price' => 35000],
        ]);

        // Completed work order
        $workOrder2 = WorkOrder::create([
            'branch_id' => $mainBranch->id,
            'vehicle_id' => $createdVehicles[2]->id,
            'customer_id' => $createdCustomers[1]->id,
            'assigned_technician_id' => $technician2->id,
            'created_by' => $cashier->id,
            'type' => 'repair',
            'status' => 'delivered',
            'is_combo' => false,
            'priority' => 'high',
            'mileage_in' => 58100,
            'mileage_out' => 58105,
            'customer_notes' => 'Squeaking noise when braking',
            'technician_notes' => 'Replaced front brake pads. Rotors in good condition.',
            'checked_in_at' => now()->subDays(1),
            'started_at' => now()->subDays(1)->addHours(2),
            'completed_at' => now()->subDays(1)->addHours(5),
            'delivered_at' => now()->subDays(1)->addHours(6),
        ]);

        $workOrder2->items()->createMany([
            ['item_type' => 'labor', 'description' => 'Brake Pad Replacement - Front', 'quantity' => 1, 'unit_price' => 80000],
            ['item_type' => 'part', 'description' => 'Brake Pads - Front', 'inventory_item_id' => InventoryItem::where('sku', 'BP-FRT-001')->first()->id, 'quantity' => 1, 'unit_price' => 120000],
        ]);

        // ========================================
        // SAMPLE WASH ORDERS
        // ========================================
        $washBay = WashBay::where('branch_id', $mainBranch->id)->where('status', 'occupied')->first();

        // Wash in progress
        $washOrder1 = WashOrder::create([
            'branch_id' => $mainBranch->id,
            'vehicle_id' => $createdVehicles[3]->id,
            'customer_id' => $createdCustomers[2]->id,
            'wash_bay_id' => $washBay?->id,
            'assigned_attendant_id' => $washAttendant->id,
            'created_by' => $cashier->id,
            'wash_type' => 'premium',
            'status' => 'in_progress',
            'source' => 'walk_in',
            'priority' => 'normal',
            'queued_at' => now()->subMinutes(45),
            'started_at' => now()->subMinutes(15),
        ]);

        $washOrder1->items()->create([
            'description' => 'Premium Wash',
            'quantity' => 1,
            'unit_price' => 40000,
        ]);

        // Wash queue
        $washOrder2 = WashOrder::create([
            'branch_id' => $mainBranch->id,
            'vehicle_id' => $createdVehicles[7]->id,
            'customer_id' => $createdCustomers[5]->id,
            'created_by' => $cashier->id,
            'wash_type' => 'standard',
            'status' => 'queued',
            'source' => 'walk_in',
            'priority' => 'normal',
            'queue_position' => 1,
            'queued_at' => now()->subMinutes(10),
        ]);

        $washOrder2->items()->create([
            'description' => 'Standard Wash',
            'quantity' => 1,
            'unit_price' => 25000,
        ]);

        // ========================================
        // SAMPLE INVOICES
        // ========================================
        $invoice1 = Invoice::create([
            'branch_id' => $mainBranch->id,
            'customer_id' => $createdCustomers[1]->id,
            'work_order_id' => $workOrder2->id,
            'created_by' => $cashier->id,
            'type' => 'service',
            'subtotal' => 200000,
            'tax_rate' => 18,
            'tax_amount' => 36000,
            'discount_amount' => 0,
            'total' => 236000,
            'amount_paid' => 236000,
            'balance_due' => 0,
            'status' => 'paid',
            'issue_date' => now()->subDay(),
            'due_date' => now()->addDays(13),
        ]);

        $invoice1->items()->createMany([
            ['item_type' => 'labor', 'description' => 'Brake Pad Replacement - Front', 'quantity' => 1, 'unit_price' => 80000, 'total' => 80000],
            ['item_type' => 'part', 'description' => 'Brake Pads - Front', 'quantity' => 1, 'unit_price' => 120000, 'total' => 120000],
        ]);

        Payment::create([
            'invoice_id' => $invoice1->id,
            'received_by' => $cashier->id,
            'amount' => 236000,
            'payment_method' => 'mobile_money',
            'provider' => 'MTN MoMo',
            'reference' => 'MM123456789',
            'status' => 'completed',
            'payment_date' => now()->subDay(),
        ]);

        // ========================================
        // SAMPLE APPOINTMENTS
        // ========================================
        Appointment::create([
            'branch_id' => $mainBranch->id,
            'customer_id' => $createdCustomers[4]->id,
            'vehicle_id' => $createdVehicles[6]->id,
            'created_by' => $cashier->id,
            'type' => 'service',
            'scheduled_date' => now()->addDay(),
            'scheduled_time' => '09:00',
            'duration_minutes' => 120,
            'status' => 'confirmed',
            'service_notes' => 'Full service for tour van',
            'confirmed_at' => now(),
        ]);

        Appointment::create([
            'branch_id' => $mainBranch->id,
            'customer_id' => $createdCustomers[6]->id,
            'vehicle_id' => $createdVehicles[8]->id,
            'created_by' => $cashier->id,
            'type' => 'combo',
            'scheduled_date' => now()->addDays(2),
            'scheduled_time' => '10:30',
            'duration_minutes' => 180,
            'status' => 'scheduled',
            'service_notes' => 'Regular service + full wash',
        ]);

        // ========================================
        // SAMPLE EXPENSES
        // ========================================
        $utilitiesCategory = ExpenseCategory::where('vendor_id', $vendor->id)->where('name', 'Utilities')->first();
        $suppliesCategory = ExpenseCategory::where('vendor_id', $vendor->id)->where('name', 'Supplies')->first();

        Expense::create([
            'branch_id' => $mainBranch->id,
            'category_id' => $utilitiesCategory->id,
            'recorded_by' => $manager->id,
            'approved_by' => $owner->id,
            'amount' => 350000,
            'payment_method' => 'bank_transfer',
            'description' => 'Electricity bill - March',
            'expense_date' => now()->subDays(5),
            'status' => 'approved',
        ]);

        Expense::create([
            'branch_id' => $mainBranch->id,
            'category_id' => $suppliesCategory->id,
            'supplier_id' => $supplier2->id,
            'recorded_by' => $storekeeper->id,
            'amount' => 500000,
            'payment_method' => 'cash',
            'description' => 'Car wash supplies restocking',
            'expense_date' => now()->subDays(3),
            'status' => 'approved',
        ]);

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('-------------------');
        $this->command->info('Platform Admin: admin@garageplatform.com / password');
        $this->command->info('Vendor Owner: john@autocaregarage.com / password');
        $this->command->info('Branch Manager: sarah@autocaregarage.com / password');
        $this->command->info('Technician: peter@autocaregarage.com / password');
        $this->command->info('Wash Attendant: grace@autocaregarage.com / password');
        $this->command->info('Cashier: mary@autocaregarage.com / password');
        $this->command->info('Storekeeper: david@autocaregarage.com / password');
    }
}
