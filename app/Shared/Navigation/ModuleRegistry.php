<?php

namespace App\Shared\Navigation;

use Closure;
use Illuminate\Support\Str;

class ModuleRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'work',
                'name' => 'Work Orders',
                'icon' => 'workOrders',
                'blurb' => 'Repairs from check-in to release — bays, technicians, job cards.',
                'permission' => ['view_work_orders', 'view_assigned_work_orders'],
                'patterns' => ['work-orders.*', 'appointments.*', 'calendar', 'bays.status'],
                'items' => [
                    ['label' => 'All work orders', 'route' => 'work-orders.index', 'icon' => 'workOrders', 'permission' => ['view_work_orders', 'view_assigned_work_orders']],
                    ['label' => 'New work order', 'route' => 'work-orders.create', 'icon' => 'checkIn', 'permission' => 'create_work_orders'],
                    ['label' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'calendar', 'permission' => 'view_appointments'],
                    ['label' => 'Calendar', 'route' => 'calendar', 'icon' => 'calendar', 'permission' => null],
                    ['label' => 'Bay status', 'route' => 'bays.status', 'icon' => 'bayStatus', 'permission' => ['view_service_bays', 'view_wash_bays']],
                ],
            ],
            [
                'key' => 'wash',
                'name' => 'Wash Bay',
                'icon' => 'washBay',
                'iconTone' => 'info',
                'blurb' => 'Wash and detailing queue, packages, combo hand-off from service.',
                'permission' => ['view_wash_orders', 'view_assigned_wash_orders'],
                'patterns' => ['wash-orders.*', 'packages.*', 'bays.status'],
                'items' => [
                    ['label' => 'Queue', 'route' => 'wash-orders.index', 'icon' => 'washBay', 'permission' => ['view_wash_orders', 'view_assigned_wash_orders']],
                    ['label' => 'New wash', 'route' => 'wash-orders.create', 'icon' => 'checkIn', 'permission' => 'create_wash_orders'],
                    ['label' => 'Packages', 'route' => 'packages.index', 'icon' => 'washBay', 'permission' => null],
                    ['label' => 'Bay status', 'route' => 'bays.status', 'icon' => 'bayStatus', 'permission' => ['view_service_bays', 'view_wash_bays']],
                ],
            ],
            [
                'key' => 'customers',
                'name' => 'Customers',
                'icon' => 'customers',
                'blurb' => 'Customers, their vehicles, service history and balances.',
                'permission' => ['view_customers', 'view_vehicles'],
                'patterns' => ['customers.*', 'vehicles.*'],
                'items' => [
                    ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'customers', 'permission' => 'view_customers'],
                    ['label' => 'Vehicles', 'route' => 'vehicles.index', 'icon' => 'vehicles', 'permission' => 'view_vehicles'],
                ],
            ],
            [
                'key' => 'finance',
                'name' => 'Finance',
                'icon' => 'expenses',
                'iconTone' => 'success',
                'blurb' => 'Quotations, invoices, payments and commissions.',
                'permission' => ['view_invoices', 'view_quotations', 'view_payments', 'view_commissions', 'view_own_commissions'],
                'patterns' => ['invoices.*', 'quotations.*', 'payments.*', 'commissions.*'],
                'items' => [
                    ['label' => 'Invoices', 'route' => 'invoices.index', 'icon' => 'invoices', 'permission' => 'view_invoices'],
                    ['label' => 'Quotations', 'route' => 'quotations.index', 'icon' => 'invoices', 'permission' => 'view_quotations'],
                    ['label' => 'Payments', 'route' => 'payments.index', 'icon' => 'payments', 'permission' => 'view_payments'],
                    ['label' => 'Commissions', 'route' => 'commissions.index', 'icon' => 'commissions', 'permission' => ['view_commissions', 'view_own_commissions']],
                ],
            ],
            [
                'key' => 'stock',
                'name' => 'Inventory',
                'icon' => 'stock',
                'iconTone' => 'neutral',
                'blurb' => 'Parts, stores, suppliers and what each job consumed.',
                'permission' => 'view_inventory',
                'patterns' => ['inventory.*', 'suppliers.*'],
                'items' => [
                    ['label' => 'All items', 'route' => 'inventory.index', 'icon' => 'stock', 'permission' => null],
                    ['label' => 'Low stock', 'route' => 'inventory.low-stock', 'icon' => 'stock', 'permission' => null],
                    ['label' => 'Movements', 'route' => 'inventory.movements', 'icon' => 'reports', 'permission' => 'adjust_stock'],
                    ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'suppliers', 'permission' => 'view_suppliers'],
                ],
            ],
            [
                'key' => 'reports',
                'name' => 'Reports',
                'icon' => 'reports',
                'iconTone' => 'neutral',
                'blurb' => 'Revenue, profitability, bay utilisation and branch comparison.',
                'permission' => 'view_reports',
                'patterns' => ['reports.*'],
                'items' => [
                    ['label' => 'Sales', 'route' => 'reports.sales', 'icon' => 'reports', 'permission' => null],
                    ['label' => 'Operations', 'route' => 'reports.operations', 'icon' => 'reports', 'permission' => null],
                    ['label' => 'Inventory', 'route' => 'reports.inventory', 'icon' => 'stock', 'permission' => null],
                    ['label' => 'Staff performance', 'route' => 'reports.staff', 'icon' => 'staff', 'permission' => null],
                    ['label' => 'Debtors', 'route' => 'reports.debtors', 'icon' => 'reports', 'permission' => 'view_debtors'],
                    ['label' => 'Creditors', 'route' => 'reports.creditors', 'icon' => 'reports', 'permission' => 'view_creditors'],
                ],
            ],
            [
                'key' => 'hr',
                'name' => 'HR & Staff',
                'icon' => 'staff',
                'iconTone' => 'info',
                'blurb' => 'Staff, roles, attendance and technician performance.',
                'permission' => ['view_users', 'manage_roles'],
                'patterns' => ['users.*', 'roles.*'],
                'items' => [
                    ['label' => 'Staff', 'route' => 'users.index', 'icon' => 'staff', 'permission' => 'view_users'],
                    ['label' => 'Roles & permissions', 'route' => 'roles.index', 'icon' => 'roles', 'permission' => 'manage_roles'],
                ],
            ],
            [
                'key' => 'settings',
                'name' => 'Settings',
                'icon' => 'settings',
                'iconTone' => 'neutral',
                'blurb' => 'Branches, service catalogue, templates and platform config.',
                'permission' => ['view_branches', 'view_service_templates', 'view_settings'],
                'patterns' => ['branches.*', 'service-types.*', 'service-categories.*', 'templates.*', 'settings'],
                'items' => [
                    ['label' => 'Branches', 'route' => 'branches.index', 'icon' => 'branches', 'permission' => 'view_branches'],
                    ['label' => 'Service types', 'route' => 'service-types.index', 'icon' => 'serviceTypes', 'permission' => 'view_service_templates'],
                    ['label' => 'Service categories', 'route' => 'service-categories.index', 'icon' => 'serviceTypes', 'permission' => 'view_service_templates'],
                    ['label' => 'Templates', 'route' => 'templates.index', 'icon' => 'templates', 'permission' => 'view_service_templates'],
                    ['label' => 'Settings', 'route' => 'settings', 'icon' => 'settings', 'permission' => 'view_settings'],
                ],
            ],
            [
                'key' => 'marketplace',
                'name' => 'Marketplace',
                'icon' => 'shop',
                'blurb' => 'Supplier quotes, listings, purchase orders and marketplace browsing.',
                'permission' => ['browse_marketplace', 'view_quotes', 'view_listings', 'view_rfqs', 'view_purchase_orders'],
                'patterns' => ['supplier.*', 'marketplace.*'],
                'items' => [
                    ['label' => 'Quote inbox', 'route' => 'supplier.quotes.inbox', 'icon' => 'shop', 'permission' => 'view_quotes'],
                    ['label' => 'My listings', 'route' => 'supplier.listings.index', 'icon' => 'shop', 'permission' => 'view_listings'],
                    ['label' => 'My orders', 'route' => 'supplier.orders.index', 'icon' => 'shop', 'permission' => 'view_purchase_orders'],
                    ['label' => 'Browse listings', 'route' => 'marketplace.browse', 'icon' => 'browse', 'permission' => 'browse_marketplace'],
                    ['label' => 'RFQs', 'route' => 'marketplace.rfqs.index', 'icon' => 'browse', 'permission' => 'view_rfqs'],
                    ['label' => 'Purchase orders', 'route' => 'marketplace.purchase-orders.index', 'icon' => 'browse', 'permission' => 'view_purchase_orders'],
                ],
            ],
            [
                'key' => 'platform',
                'name' => 'Platform',
                'icon' => 'branches',
                'blurb' => 'Vendor accounts, billing and API integrations across the platform.',
                'permission' => fn ($user) => $user && method_exists($user, 'isPlatformUser') && $user->isPlatformUser(),
                'patterns' => ['platform.*'],
                'items' => [
                    ['label' => 'Vendors', 'route' => 'platform.vendors.index', 'icon' => 'branches', 'permission' => null],
                    ['label' => 'Billing', 'route' => 'platform.billing', 'icon' => 'billing', 'permission' => null],
                    ['label' => 'Integrations', 'route' => 'platform.integrations.index', 'icon' => 'integrations', 'permission' => null],
                ],
            ],
        ];
    }

    /**
     * Resolve the module the given route name belongs to, if any.
     *
     * @return array<string, mixed>|null
     */
    public static function current(?string $routeName): ?array
    {
        if (! $routeName) {
            return null;
        }

        foreach (static::all() as $module) {
            foreach ($module['patterns'] as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return $module;
                }
            }
        }

        return null;
    }

    /**
     * Modules the current user may see, filtered to those with at least one visible item.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function visibleModules(): array
    {
        return array_values(array_filter(
            static::all(),
            fn (array $module) => static::isModuleAuthorized($module) && count(static::visibleItems($module)) > 0
        ));
    }

    /**
     * Items within a module the current user may see. Item-level permissions
     * narrow further, but never bypass the module-level gate.
     *
     * @param  array<string, mixed>  $module
     * @return array<int, array<string, mixed>>
     */
    public static function visibleItems(array $module): array
    {
        if (! static::isModuleAuthorized($module)) {
            return [];
        }

        return array_values(array_filter(
            $module['items'],
            fn (array $item) => static::authorized($item['permission'])
        ));
    }

    /**
     * @param  array<string, mixed>  $module
     */
    public static function isModuleAuthorized(array $module): bool
    {
        return static::authorized($module['permission']);
    }

    public static function authorized(null|string|array|Closure $permission): bool
    {
        if ($permission === null) {
            return true;
        }

        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($permission instanceof Closure) {
            return (bool) $permission($user);
        }

        if (is_array($permission)) {
            foreach ($permission as $ability) {
                if ($user->can($ability)) {
                    return true;
                }
            }

            return false;
        }

        return $user->can($permission);
    }
}
