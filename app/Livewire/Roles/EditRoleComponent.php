<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EditRoleComponent extends Component
{
    public Role $role;
    public array $selectedPermissions = [];

    // Permissions grouped by module for display
    public array $permissionGroups = [
        'Dashboard & Reports'     => ['view_dashboard', 'view_reports', 'export_reports'],
        'Vendors'                 => ['view_vendors', 'create_vendors', 'edit_vendors', 'delete_vendors', 'manage_vendor_billing'],
        'Branches'                => ['view_branches', 'create_branches', 'edit_branches', 'delete_branches'],
        'Users & Roles'           => ['view_users', 'create_users', 'edit_users', 'delete_users', 'assign_roles', 'manage_roles'],
        'Customers'               => ['view_customers', 'create_customers', 'edit_customers', 'delete_customers', 'manage_customer_credit'],
        'Vehicles'                => ['view_vehicles', 'create_vehicles', 'edit_vehicles', 'delete_vehicles'],
        'Service Bays'            => ['view_service_bays', 'create_service_bays', 'edit_service_bays', 'delete_service_bays', 'manage_bays'],
        'Wash Bays'               => ['view_wash_bays', 'create_wash_bays', 'edit_wash_bays', 'delete_wash_bays'],
        'Work Orders'             => ['view_work_orders', 'create_work_orders', 'edit_work_orders', 'delete_work_orders', 'assign_work_orders', 'change_work_order_status', 'view_assigned_work_orders', 'price_work_orders'],
        'Wash Orders'             => ['view_wash_orders', 'create_wash_orders', 'edit_wash_orders', 'delete_wash_orders', 'assign_wash_orders', 'change_wash_order_status', 'manage_wash_queue', 'view_assigned_wash_orders'],
        'Invoices'                => ['view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices', 'send_invoices', 'void_invoices'],
        'Payments'                => ['view_payments', 'receive_payments', 'refund_payments'],
        'Inventory'               => ['view_inventory', 'create_inventory', 'edit_inventory', 'delete_inventory', 'adjust_stock', 'transfer_stock', 'view_low_stock'],
        'Suppliers'               => ['view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers'],
        'Expenses'                => ['view_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses', 'approve_expenses'],
        'Commissions'             => ['view_commissions', 'manage_commission_rules', 'approve_commissions', 'pay_commissions', 'view_own_commissions'],
        'Appointments'            => ['view_appointments', 'create_appointments', 'edit_appointments', 'delete_appointments', 'confirm_appointments'],
        'Service Templates'       => ['view_service_templates', 'create_service_templates', 'edit_service_templates', 'delete_service_templates'],
        'Wash Packages'           => ['view_wash_packages', 'create_wash_packages', 'edit_wash_packages', 'delete_wash_packages'],
        'Settings & Audit'        => ['view_settings', 'edit_settings', 'view_audit_logs'],
        'Pricing & Calendar'      => ['price_work_orders', 'view_calendar'],
    ];

    public function mount(int $roleId): void
    {
        $this->role = Role::findOrFail($roleId);
        $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();
    }

    public function save(): void
    {
        $this->authorize('manage_roles');

        // Prevent changing super-admin permissions
        if ($this->role->name === 'super-admin') {
            session()->flash('error', 'Super-admin permissions cannot be modified.');
            return;
        }

        // Filter to only permissions that actually exist
        $valid = Permission::whereIn('name', $this->selectedPermissions)->pluck('name')->toArray();
        $this->role->syncPermissions($valid);

        session()->flash('success', 'Permissions updated for ' . $this->role->name . '.');
    }

    public function toggleGroup(string $group): void
    {
        $groupPerms = $this->permissionGroups[$group] ?? [];
        $allSelected = count(array_intersect($groupPerms, $this->selectedPermissions)) === count($groupPerms);

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $groupPerms));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $groupPerms)));
        }
    }

    public function render()
    {
        return view('livewire.roles.edit-role-component')
            ->layout('components.layouts.app', ['title' => 'Edit Role: ' . $this->role->name]);
    }
}
