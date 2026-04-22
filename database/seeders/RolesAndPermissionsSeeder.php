<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions by module
        $permissions = [
            // Dashboard
            'view dashboard',
            'view reports',
            'export reports',

            // Vendors (platform level)
            'view vendors',
            'create vendors',
            'edit vendors',
            'delete vendors',
            'manage vendor billing',

            // Branches
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',

            // Users/Staff
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',

            // Customers
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'manage customer credit',

            // Vehicles
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',

            // Service Bays
            'view service bays',
            'create service bays',
            'edit service bays',
            'delete service bays',
            'manage bay status',

            // Wash Bays
            'view wash bays',
            'create wash bays',
            'edit wash bays',
            'delete wash bays',

            // Work Orders
            'view work orders',
            'create work orders',
            'edit work orders',
            'delete work orders',
            'assign work orders',
            'change work order status',
            'view assigned work orders',

            // Wash Orders
            'view wash orders',
            'create wash orders',
            'edit wash orders',
            'delete wash orders',
            'assign wash orders',
            'change wash order status',
            'manage wash queue',
            'view assigned wash orders',

            // Invoices
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'send invoices',
            'void invoices',

            // Payments
            'view payments',
            'receive payments',
            'refund payments',

            // Inventory
            'view inventory',
            'create inventory',
            'edit inventory',
            'delete inventory',
            'adjust stock',
            'transfer stock',
            'view low stock',

            // Suppliers
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',

            // Expenses
            'view expenses',
            'create expenses',
            'edit expenses',
            'delete expenses',
            'approve expenses',

            // Commissions
            'view commissions',
            'manage commission rules',
            'approve commissions',
            'pay commissions',
            'view own commissions',

            // Appointments
            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',
            'confirm appointments',

            // Service Templates
            'view service templates',
            'create service templates',
            'edit service templates',
            'delete service templates',

            // Wash Packages
            'view wash packages',
            'create wash packages',
            'edit wash packages',
            'delete wash packages',

            // Settings
            'view settings',
            'edit settings',

            // Audit Logs
            'view audit logs',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // ========================================
        // PLATFORM ROLES
        // ========================================

        // Super Admin - full platform access
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Platform Support - read-only platform access
        $platformSupport = Role::create(['name' => 'platform-support']);
        $platformSupport->givePermissionTo([
            'view dashboard',
            'view vendors',
            'view reports',
            'view audit logs',
        ]);

        // ========================================
        // VENDOR ROLES
        // ========================================

        // Vendor Owner - full access to their vendor
        $vendorOwner = Role::create(['name' => 'vendor-owner']);
        $vendorOwner->givePermissionTo([
            'view dashboard',
            'view reports',
            'export reports',
            // Branches
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',
            // Users
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
            // Customers
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'manage customer credit',
            // Vehicles
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
            // Service Bays
            'view service bays',
            'create service bays',
            'edit service bays',
            'delete service bays',
            'manage bay status',
            // Wash Bays
            'view wash bays',
            'create wash bays',
            'edit wash bays',
            'delete wash bays',
            // Work Orders
            'view work orders',
            'create work orders',
            'edit work orders',
            'delete work orders',
            'assign work orders',
            'change work order status',
            // Wash Orders
            'view wash orders',
            'create wash orders',
            'edit wash orders',
            'delete wash orders',
            'assign wash orders',
            'change wash order status',
            'manage wash queue',
            // Invoices
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'send invoices',
            'void invoices',
            // Payments
            'view payments',
            'receive payments',
            'refund payments',
            // Inventory
            'view inventory',
            'create inventory',
            'edit inventory',
            'delete inventory',
            'adjust stock',
            'transfer stock',
            'view low stock',
            // Suppliers
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            // Expenses
            'view expenses',
            'create expenses',
            'edit expenses',
            'delete expenses',
            'approve expenses',
            // Commissions
            'view commissions',
            'manage commission rules',
            'approve commissions',
            'pay commissions',
            // Appointments
            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',
            'confirm appointments',
            // Templates
            'view service templates',
            'create service templates',
            'edit service templates',
            'delete service templates',
            'view wash packages',
            'create wash packages',
            'edit wash packages',
            'delete wash packages',
            // Settings
            'view settings',
            'edit settings',
            // Audit
            'view audit logs',
        ]);

        // Branch Manager - manages a specific branch
        $branchManager = Role::create(['name' => 'branch-manager']);
        $branchManager->givePermissionTo([
            'view dashboard',
            'view reports',
            // Users (limited)
            'view users',
            // Customers
            'view customers',
            'create customers',
            'edit customers',
            // Vehicles
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            // Bays
            'view service bays',
            'manage bay status',
            'view wash bays',
            // Work Orders
            'view work orders',
            'create work orders',
            'edit work orders',
            'assign work orders',
            'change work order status',
            // Wash Orders
            'view wash orders',
            'create wash orders',
            'edit wash orders',
            'assign wash orders',
            'change wash order status',
            'manage wash queue',
            // Invoices
            'view invoices',
            'create invoices',
            'edit invoices',
            'send invoices',
            // Payments
            'view payments',
            'receive payments',
            // Inventory
            'view inventory',
            'adjust stock',
            'view low stock',
            // Expenses
            'view expenses',
            'create expenses',
            'edit expenses',
            'approve expenses',
            // Commissions
            'view commissions',
            'approve commissions',
            // Appointments
            'view appointments',
            'create appointments',
            'edit appointments',
            'confirm appointments',
            // Templates (view only)
            'view service templates',
            'view wash packages',
        ]);

        // Technician - handles service work orders
        $technician = Role::create(['name' => 'technician']);
        $technician->givePermissionTo([
            'view dashboard',
            // Customers (view only)
            'view customers',
            // Vehicles
            'view vehicles',
            // Bays
            'view service bays',
            'manage bay status',
            // Work Orders
            'view assigned work orders',
            'change work order status',
            // Inventory (view for parts)
            'view inventory',
            // Templates
            'view service templates',
            // Commissions
            'view own commissions',
        ]);

        // Wash Attendant - handles wash orders
        $washAttendant = Role::create(['name' => 'wash-attendant']);
        $washAttendant->givePermissionTo([
            'view dashboard',
            // Customers (view only)
            'view customers',
            // Vehicles
            'view vehicles',
            // Wash Bays
            'view wash bays',
            // Wash Orders
            'view assigned wash orders',
            'change wash order status',
            // Packages
            'view wash packages',
            // Commissions
            'view own commissions',
        ]);

        // Cashier - handles payments and invoicing
        $cashier = Role::create(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'view dashboard',
            // Customers
            'view customers',
            'create customers',
            'edit customers',
            // Vehicles
            'view vehicles',
            'create vehicles',
            // Work Orders (view)
            'view work orders',
            // Wash Orders (view)
            'view wash orders',
            // Invoices
            'view invoices',
            'create invoices',
            'edit invoices',
            'send invoices',
            // Payments
            'view payments',
            'receive payments',
            // Appointments
            'view appointments',
            'create appointments',
            'edit appointments',
            'confirm appointments',
        ]);

        // Storekeeper - manages inventory
        $storekeeper = Role::create(['name' => 'storekeeper']);
        $storekeeper->givePermissionTo([
            'view dashboard',
            // Inventory
            'view inventory',
            'create inventory',
            'edit inventory',
            'adjust stock',
            'transfer stock',
            'view low stock',
            // Suppliers
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            // Expenses (for purchases)
            'view expenses',
            'create expenses',
        ]);
    }
}
