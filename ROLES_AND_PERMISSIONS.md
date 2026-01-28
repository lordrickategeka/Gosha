# Roles and Permissions Structure

## Overview
This document outlines the roles and permissions structure for the ISDTS Core application.

**Total Roles:** 13  
**Total Permissions:** 62

---

## Roles

### 1. Sales
**Description:** Standard sales team member  
**Permissions:**
- `view_clients`
- `create_clients`
- `edit_clients`
- `view_services`
- `view_products`
- `create_quotations`
- `view_sales_reports`
- `view_sales_pipeline`
- `manage_deals`
- `access_dashboard`

### 2. Sales Manager
**Description:** Sales team manager  
**Permissions:**
- `view_clients`
- `create_clients`
- `edit_clients`
- `delete_clients`
- `view_services`
- `assign_services`
- `view_products`
- `create_quotations`
- `approve_quotations`
- `view_sales_reports`
- `view_sales_pipeline`
- `manage_deals`
- `access_dashboard`
- `view_kpi_metrics`
- `export_clients`
- `export_reports`

### 3. Customer Experience (CX)
**Description:** CX  
**Permissions:**
- `view_clients`
- `edit_clients`
- `view_services`
- `view_forms`
- `view_submissions`
- `access_dashboard`
- `view_analytics`

### 4. Chief Commercial Officer (CCO)
**Description:** CCO  
**Permissions:**
- `view_clients`
- `create_clients`
- `edit_clients`
- `delete_clients`
- `approve_clients`
- `export_clients`
- `view_services`
- `create_services`
- `edit_services`
- `delete_services`
- `assign_services`
- `view_products`
- `create_products`
- `edit_products`
- `manage_pricing`
- `view_vendors`
- `create_vendors`
- `edit_vendors`
- `view_sales_reports`
- `create_quotations`
- `approve_quotations`
- `manage_deals`
- `view_sales_pipeline`
- `view_analytics`
- `generate_reports`
- `export_reports`
- `access_dashboard`
- `view_kpi_metrics`

### 5. Credit Control Manager (CCM)
**Description:** CCM  
**Permissions:**
- `view_clients`
- `view_financial_reports`
- `manage_payments`
- `approve_invoices`
- `manage_credit`
- `view_analytics`
- `generate_reports`
- `export_reports`
- `access_dashboard`

### 6. Chief Financial Officer (CFO)
**Description:** CFO  
**Permissions:**
- `view_clients`
- `view_services`
- `view_products`
- `view_vendors`
- `approve_vendors`
- `view_financial_reports`
- `manage_payments`
- `approve_invoices`
- `manage_credit`
- `view_revenue_reports`
- `access_financial_dashboard`
- `view_analytics`
- `generate_reports`
- `export_reports`
- `access_dashboard`
- `view_kpi_metrics`
- `manage_pricing`

### 7. Business Analyst (BA0)
**Description:** BA0  
**Permissions:**
- `view_clients`
- `view_services`
- `view_products`
- `view_vendors`
- `view_analytics`
- `generate_reports`
- `export_reports`
- `access_dashboard`
- `view_kpi_metrics`
- `view_sales_reports`
- `view_financial_reports`
- `view_network_reports`

### 8. Network Planning Officer (NPO)
**Description:** NPO  
**Permissions:**
- `view_clients`
- `view_services`
- `view_products`
- `view_vendors`
- `view_network_reports`
- `plan_network`
- `access_technical_data`
- `access_dashboard`
- `generate_reports`

### 9. Network Implementation Officer (NIO)
**Description:** NIO  
**Permissions:**
- `view_clients`
- `view_services`
- `view_products`
- `view_vendors`
- `view_network_reports`
- `approve_implementations`
- `manage_installations`
- `access_technical_data`
- `access_dashboard`

### 10. Director (CEO)
**Description:** CEO  
**Permissions:** **All Permissions (62 total)**

---

## All Available Permissions

### Client Management
- `view_clients` - View client information
- `create_clients` - Create new clients
- `edit_clients` - Edit existing clients
- `delete_clients` - Delete clients
- `approve_clients` - Approve client registrations
- `export_clients` - Export client data

### Service Management
- `view_services` - View services
- `create_services` - Create new services
- `edit_services` - Edit existing services
- `delete_services` - Delete services
- `assign_services` - Assign services to clients

### Product Management
- `view_products` - View products
- `create_products` - Create new products
- `edit_products` - Edit existing products
- `delete_products` - Delete products
- `manage_pricing` - Manage product pricing

### Vendor Management
- `view_vendors` - View vendors
- `create_vendors` - Create new vendors
- `edit_vendors` - Edit existing vendors
- `delete_vendors` - Delete vendors
- `approve_vendors` - Approve vendor registrations

### Financial Management
- `view_financial_reports` - View financial reports
- `manage_payments` - Manage payments
- `approve_invoices` - Approve invoices
- `manage_credit` - Manage credit terms
- `view_revenue_reports` - View revenue reports
- `access_financial_dashboard` - Access financial dashboard

### Sales Management
- `view_sales_reports` - View sales reports
- `create_quotations` - Create quotations
- `approve_quotations` - Approve quotations
- `manage_deals` - Manage sales deals
- `view_sales_pipeline` - View sales pipeline

### Network Operations
- `view_network_reports` - View network reports
- `plan_network` - Plan network infrastructure
- `approve_implementations` - Approve network implementations
- `manage_installations` - Manage installations
- `access_technical_data` - Access technical data

### Business Analysis
- `view_analytics` - View analytics
- `generate_reports` - Generate reports
- `export_reports` - Export reports
- `access_dashboard` - Access dashboard
- `view_kpi_metrics` - View KPI metrics

### User Management
- `view_users` - View users
- `create_users` - Create new users
- `edit_users` - Edit existing users
- `delete_users` - Delete users
- `assign_roles` - Assign roles to users

### Role & Permission Management
- `view_roles` - View roles
- `create_roles` - Create new roles
- `edit_roles` - Edit existing roles
- `delete_roles` - Delete roles
- `manage_permissions` - Manage permissions

### Form Management
- `view_forms` - View forms
- `create_forms` - Create new forms
- `edit_forms` - Edit existing forms
- `delete_forms` - Delete forms
- `view_submissions` - View form submissions

### System Settings
- `manage_settings` - Manage system settings
- `view_audit_logs` - View audit logs
- `manage_system_config` - Manage system configuration

---

## Usage

### Running the Seeder

To seed roles and permissions:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Assigning Roles to Users

```php
use App\Models\User;
use Spatie\Permission\Models\Role;

// Assign a role to a user
$user = User::find(1);
$user->assignRole('sales');

// Assign multiple roles
$user->assignRole(['sales', 'customer_experience']);

// Remove a role
$user->removeRole('sales');

// Sync roles (removes all other roles)
$user->syncRoles(['sales_manager']);
```

### Checking Permissions

```php
// Check if user has permission
if ($user->can('create_clients')) {
    // User has permission
}

// Check if user has role
if ($user->hasRole('sales_manager')) {
    // User has role
}

// In Blade templates
@can('create_clients')
    <!-- Show create button -->
@endcan

@role('sales_manager')
    <!-- Show manager-only content -->
@endrole
```

### Protecting Routes

```php
Route::middleware(['auth', 'permission:create_clients'])->group(function () {
    Route::get('/clients/create', [ClientController::class, 'create']);
});

Route::middleware(['auth', 'role:sales_manager'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
});
```

---

## Notes

- The seeder uses `firstOrCreate()` and `syncPermissions()` to avoid duplicates and allow re-running
- CEO/Director role has access to all permissions in the system
- Permissions can be added or modified in the seeder file: `database/seeders/RolesAndPermissionsSeeder.php`
- Role descriptions are stored in the `description` column of the `roles` table
