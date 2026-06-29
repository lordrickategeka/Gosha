<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MarketplaceRolesSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            // Catalog
            'view_catalog', 'manage_catalog', 'verify_catalog_products',
            // Listings (supplier side)
            'view_listings', 'manage_listings',
            // RFQ (buyer side)
            'view_rfqs', 'manage_rfqs', 'award_quotes',
            // Quotes (supplier side)
            'view_quotes', 'manage_quotes',
            // Purchase orders
            'view_purchase_orders', 'manage_purchase_orders', 'receive_goods',
            // Marketplace browse + buy (buyer side)
            'browse_marketplace', 'place_marketplace_orders',
            // Commission / transactions (platform admin)
            'view_marketplace_transactions',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, $guard);
        }

        // Supplier roles + dashboard scope.
        $supplierAdmin = Role::findOrCreate('supplier_admin', $guard);
        $supplierAdmin->givePermissionTo([
            'view_catalog', 'manage_catalog',
            'view_listings', 'manage_listings',
            'view_quotes', 'manage_quotes',
            'view_purchase_orders', 'manage_purchase_orders',
        ]);

        $supplierStaff = Role::findOrCreate('supplier_staff', $guard);
        $supplierStaff->givePermissionTo([
            'view_catalog', 'view_listings', 'manage_listings',
            'view_quotes', 'manage_quotes', 'view_purchase_orders',
        ]);

        // Buyer-side permissions attach to existing garage roles.
        foreach (['garage_admin', 'branch_manager'] as $roleName) {
            if ($role = Role::where('name', $roleName)->where('guard_name', $guard)->first()) {
                $role->givePermissionTo([
                    'browse_marketplace', 'place_marketplace_orders',
                    'view_rfqs', 'manage_rfqs', 'award_quotes',
                    'view_purchase_orders', 'manage_purchase_orders', 'receive_goods',
                    'view_catalog',
                ]);
            }
        }

        // Platform admin sees commission ledger + can verify catalog.
        if ($pa = Role::where('name', 'platform_admin')->where('guard_name', $guard)->first()) {
            $pa->givePermissionTo(['view_marketplace_transactions', 'verify_catalog_products', 'manage_catalog']);
        }
    }
}
