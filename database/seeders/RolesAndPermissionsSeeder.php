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
            'view_dashboard',
            'view_reports',
            'export_reports',
            'view_debtors',
            'manage_debtors',
            'view_creditors',
            'manage_creditors',

            // Vendors (platform level)
            'view_vendors',
            'create_vendors',
            'edit_vendors',
            'delete_vendors',
            'manage_vendor_billing',

            // Branches
            'view_branches',
            'create_branches',
            'edit_branches',
            'delete_branches',

            // Users/Staff
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'assign_roles',
            'manage_roles',

            // Customers
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            'manage_customer_credit',

            // Vehicles
            'view_vehicles',
            'create_vehicles',
            'edit_vehicles',
            'delete_vehicles',

            // Service Bays
            'view_service_bays',
            'create_service_bays',
            'edit_service_bays',
            'delete_service_bays',
            'manage_bays',

            // Wash Bays
            'view_wash_bays',
            'create_wash_bays',
            'edit_wash_bays',
            'delete_wash_bays',

            // Work Orders
            'view_work_orders',
            'create_work_orders',
            'edit_work_orders',
            'delete_work_orders',
            'assign_work_orders',
            'change_work_order_status',
            'view_assigned_work_orders',

            // Wash Orders
            'view_wash_orders',
            'create_wash_orders',
            'edit_wash_orders',
            'delete_wash_orders',
            'assign_wash_orders',
            'change_wash_order_status',
            'manage_wash_queue',
            'view_assigned_wash_orders',

            // Invoices
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',
            'send_invoices',
            'void_invoices',

            // Payments
            'view_payments',
            'receive_payments',
            'refund_payments',

            // Inventory
            'view_inventory',
            'create_inventory',
            'edit_inventory',
            'delete_inventory',
            'adjust_stock',
            'transfer_stock',
            'view_low_stock',

            // Suppliers
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',

            // Expenses
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'delete_expenses',
            'approve_expenses',

            // Commissions
            'view_commissions',
            'manage_commission_rules',
            'approve_commissions',
            'pay_commissions',
            'view_own_commissions',

            // Appointments
            'view_appointments',
            'create_appointments',
            'edit_appointments',
            'delete_appointments',
            'confirm_appointments',

            // Service Templates
            'view_service_templates',
            'create_service_templates',
            'edit_service_templates',
            'delete_service_templates',

            // Wash Packages
            'view_wash_packages',
            'create_wash_packages',
            'edit_wash_packages',
            'delete_wash_packages',

            // Settings
            'view_settings',
            'edit_settings',

            // Audit Logs
            'view_audit_logs',

            // Pricing / Quoting
            'price_work_orders',
            'view_calendar',

            // Quotations
            'view_quotations',
            'create_quotations',
            'edit_quotations',
            'send_quotations',
            'approve_quotations',
            'delete_quotations',

            // Quality Check
            'quality-check.create',
            'quality-check.view',
            'quality-check.update',
            'quality-check.download-pdf',
            'quality-check.upload-signed',
        ];



        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $syncRole = function (string $roleName, array $rolePermissions): Role {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);

            return $role;
        };

        // ========================================
        // PLATFORM ROLES
        // ========================================

        // Super Admin - full platform access
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Platform Support - read-only platform access
        $platformSupport = $syncRole('platform-support', [
            'view_dashboard',
            'view_vendors',
            'view_reports',
            'view_audit_logs',
        ]);

        // ========================================
        // VENDOR ROLES
        // ========================================

        // Vendor Owner - full access to their vendor
        $vendorOwner = $syncRole('vendor-owner', [
            'view_dashboard',
            'view_reports',
            'export_reports',
            'view_debtors',
            'manage_debtors',
            'view_creditors',
            'manage_creditors',
            'manage_roles',
            // Branches
            'view_branches',
            'create_branches',
            'edit_branches',
            'delete_branches',
            // Users
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'assign_roles',
            // Customers
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            'manage_customer_credit',
            // Vehicles
            'view_vehicles',
            'create_vehicles',
            'edit_vehicles',
            'delete_vehicles',
            // Service Bays
            'view_service_bays',
            'create_service_bays',
            'edit_service_bays',
            'delete_service_bays',
            'manage_bays',
            // Wash Bays
            'view_wash_bays',
            'create_wash_bays',
            'edit_wash_bays',
            'delete_wash_bays',
            // Work Orders
            'view_work_orders',
            'create_work_orders',
            'edit_work_orders',
            'delete_work_orders',
            'assign_work_orders',
            'change_work_order_status',
            // Wash Orders
            'view_wash_orders',
            'create_wash_orders',
            'edit_wash_orders',
            'delete_wash_orders',
            'assign_wash_orders',
            'change_wash_order_status',
            'manage_wash_queue',
            // Invoices
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',
            'send_invoices',
            'void_invoices',
            // Payments
            'view_payments',
            'receive_payments',
            'refund_payments',
            // Inventory
            'view_inventory',
            'create_inventory',
            'edit_inventory',
            'delete_inventory',
            'adjust_stock',
            'transfer_stock',
            'view_low_stock',
            // Suppliers
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            // Expenses
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'delete_expenses',
            'approve_expenses',
            // Commissions
            'view_commissions',
            'manage_commission_rules',
            'approve_commissions',
            'pay_commissions',
            // Appointments
            'view_appointments',
            'create_appointments',
            'edit_appointments',
            'delete_appointments',
            'confirm_appointments',
            // Templates
            'view_service_templates',
            'create_service_templates',
            'edit_service_templates',
            'delete_service_templates',
            'view_wash_packages',
            'create_wash_packages',
            'edit_wash_packages',
            'delete_wash_packages',
            // Settings
            'view_settings',
            'edit_settings',
            // Audit
            'view_audit_logs',
            // Quotations
            'view_quotations',
            'create_quotations',
            'edit_quotations',
            'send_quotations',
            'approve_quotations',
            'delete_quotations',
            // Quality Check
            'quality-check.create',
            'quality-check.view',
            'quality-check.update',
            'quality-check.download-pdf',
            'quality-check.upload-signed',
        ]);

        // Branch Manager - manages a specific branch
        $branchManager = $syncRole('branch-manager', [
            'view_dashboard',
            'view_reports',
            'view_debtors',
            'view_creditors',
            // Users (limited)
            'view_users',
            // Customers
            'view_customers',
            'create_customers',
            'edit_customers',
            // Vehicles
            'view_vehicles',
            'create_vehicles',
            'edit_vehicles',
            // Bays
            'view_service_bays',
            'manage_bays',
            'view_wash_bays',
            // Work Orders
            'view_work_orders',
            'create_work_orders',
            'edit_work_orders',
            'assign_work_orders',
            'change_work_order_status',
            // Wash Orders
            'view_wash_orders',
            'create_wash_orders',
            'edit_wash_orders',
            'assign_wash_orders',
            'change_wash_order_status',
            'manage_wash_queue',
            // Invoices
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'send_invoices',
            // Payments
            'view_payments',
            'receive_payments',
            // Inventory
            'view_inventory',
            'adjust_stock',
            'view_low_stock',
            // Expenses
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'approve_expenses',
            // Commissions
            'view_commissions',
            'approve_commissions',
            // Appointments
            'view_appointments',
            'create_appointments',
            'edit_appointments',
            'confirm_appointments',
            // Templates (view only)
            'view_service_templates',
            'view_wash_packages',
            // Quality Check
            'quality-check.create',
            'quality-check.view',
            'quality-check.update',
            'quality-check.download-pdf',
            'quality-check.upload-signed',
        ]);

        // Technician - handles service work orders
        $technician = $syncRole('technician', [
            'view_dashboard',
            // Customers (view only)
            'view_customers',
            // Vehicles
            'view_vehicles',
            // Bays
            'view_service_bays',
            'manage_bays',
            // Work Orders
            'view_assigned_work_orders',
            'change_work_order_status',
            // Inventory (view for parts)
            'view_inventory',
            // Templates
            'view_service_templates',
            // Commissions
            'view_own_commissions',
            // Quality Check
            'quality-check.view',
        ]);

        // Wash Attendant - handles wash orders
        $washAttendant = $syncRole('wash-attendant', [
            'view_dashboard',
            // Customers (view only)
            'view_customers',
            // Vehicles
            'view_vehicles',
            // Wash Bays
            'view_wash_bays',
            // Wash Orders
            'view_assigned_wash_orders',
            'change_wash_order_status',
            // Packages
            'view_wash_packages',
            // Commissions
            'view_own_commissions',
        ]);

        // Cashier - handles payments and invoicing
        $cashier = $syncRole('cashier', [
            'view_dashboard',
            // Customers
            'view_customers',
            'create_customers',
            'edit_customers',
            // Vehicles
            'view_vehicles',
            'create_vehicles',
            // Work Orders (view)
            'view_work_orders',
            // Wash Orders (view)
            'view_wash_orders',
            // Invoices
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'send_invoices',
            // Payments
            'view_payments',
            'receive_payments',
            'view_debtors',
            // Appointments
            'view_appointments',
            'create_appointments',
            'edit_appointments',
            'confirm_appointments',
        ]);

        // Storekeeper - manages inventory
        $storekeeper = $syncRole('storekeeper', [
            'view_dashboard',
            // Inventory
            'view_inventory',
            'create_inventory',
            'edit_inventory',
            'adjust_stock',
            'transfer_stock',
            'view_low_stock',
            // Suppliers
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            // Expenses (for purchases)
            'view_expenses',
            'create_expenses',
            'view_creditors',
            'manage_creditors',
        ]);

        // Jobcarder - captures job details and items (no pricing)
        $jobcarder = $syncRole('jobcarder', [
            'view_dashboard',
            'view_customers',
            'create_customers',
            'view_vehicles',
            'create_vehicles',
            'view_work_orders',
            'create_work_orders',
            'edit_work_orders',
            'change_work_order_status',
            'view_inventory',
            'view_service_templates',
        ]);

        // Quoter - sets prices on items captured by the jobcarder
        $quoter = $syncRole('quoter', [
            'view_dashboard',
            'view_customers',
            'view_vehicles',
            'view_work_orders',
            'edit_work_orders',
            'price_work_orders',
            'view_invoices',
            'create_invoices',
            'view_service_templates',
            'view_inventory',
            // Quotations
            'view_quotations',
            'create_quotations',
            'edit_quotations',
            'send_quotations',
            'approve_quotations',
        ]);
    }
}
