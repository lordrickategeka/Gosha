<?php

namespace App\Support;

use App\Domains\Commissions\Models\CommissionRule;
use App\Domains\Inventory\Models\InventoryCategory;
use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Inventory\Models\InventoryType;
use App\Domains\Inventory\Models\Supplier;
use App\Domains\Operations\Models\ServiceBay;
use App\Domains\Operations\Models\WashBay;
use App\Domains\Organization\Models\Branch;
use App\Domains\ServiceConfig\Models\QualityCheckTemplate;
use App\Domains\ServiceConfig\Models\ServiceCategory;
use App\Domains\ServiceConfig\Models\ServiceTemplate;
use App\Domains\ServiceConfig\Models\ServiceType;
use App\Domains\ServiceConfig\Models\WashPackage;
use App\Models\User;

class MasterData
{
    public function items(User $user): array
    {
        $vendor = $user->vendor;
        if (! $vendor || $user->is_platform_user || $vendor->vendor_type === 'supplier') {
            return [];
        }
        $vendorId = $vendor->id;
        $branches = Branch::where('vendor_id', $vendorId)->where('is_active', true)->pluck('id');
        $branch = $branches->contains((int) session('current_branch_id')) ? (int) session('current_branch_id') : $branches->first();
        $washCount = WashPackage::where('vendor_id', $vendorId)->where('is_active', true)->count();
        $washBays = WashBay::whereIn('branch_id', $branches)->count();
        $offersWash = $vendor->vendor_type === 'car_wash' || WashPackage::where('vendor_id', $vendorId)->exists() || $washBays > 0;
        $offersRepair = $vendor->vendor_type !== 'car_wash';

        $item = fn ($id, $label, $description, $example, $route, $parameters, $permission, $count, $required = true, $scope = 'Your business') => compact('id', 'label', 'description', 'example', 'route', 'parameters', 'permission', 'count', 'required', 'scope') + ['ready' => $count > 0];
        $items = [
            $item('profile', 'Business profile', 'The contact details used on business documents.', 'Business name, email, phone and address.', 'settings', ['tab' => 'general'], 'view_settings', filled($vendor->name) && filled($vendor->email) && filled($vendor->phone) && filled($vendor->address) ? 1 : 0),
            $item('branches', 'Branches', 'The locations where your team works.', 'Main workshop or a second wash location.', 'branches.index', [], 'view_branches', $branches->count()),
            $item('service-categories', 'Service categories', 'Broad labels for organising your service terminology.', 'Maintenance, Brakes, Electrical or Detailing.', 'service-categories.index', [], 'view_service_templates', ServiceCategory::where('is_active', true)->count(), $offersRepair, 'Shared reference list'),
            $item('service-types', 'Service types', 'Named services with a price and estimated time.', 'Oil change: your approved price and 45 minutes.', 'service-types.index', [], 'view_service_templates', ServiceType::count(), $offersRepair, 'Shared reference list'),
            $item('service-templates', 'Service templates', 'Reusable labour and parts lines for repair work orders.', 'Oil service: oil-change labour, oil and filter.', 'templates.index', ['tab' => 'service'], 'view_service_templates', ServiceTemplate::where('vendor_id', $vendorId)->where('is_active', true)->count(), $offersRepair),
            $item('wash-packages', 'Wash packages', 'Priced bundles of washing or detailing work.', 'Standard wash: body wash, wheels and vacuum.', 'packages.index', [], 'view_service_templates', $washCount, $offersWash),
            $item('quality-checklists', 'Quality checklist items', 'Inspection prompts used when preparing a work order for release.', 'Exterior: lights checked; Engine: no visible leaks.', 'templates.index', ['tab' => 'quality'], 'view_service_templates', QualityCheckTemplate::withoutVendorScope()->where('is_active', true)->where(fn ($q) => $q->where('vendor_id', $vendorId)->orWhereNull('vendor_id'))->count(), $offersRepair, 'Your items + system defaults'),
            $item('service-bays', 'Service bays', 'The physical repair spaces in the selected branch.', 'General repair bay or diagnostics bay.', 'bays.status', [], 'manage_bays', ServiceBay::where('branch_id', $branch)->count(), $offersRepair, 'Current branch'),
            $item('wash-bays', 'Wash bays', 'The physical washing spaces in the selected branch.', 'Standard wash bay or detailing bay.', 'bays.status', [], 'manage_bays', WashBay::where('branch_id', $branch)->count(), $offersWash, 'Current branch'),
            $item('inventory-categories', 'Inventory categories', 'Groups for parts, wash supplies, tools and consumables.', 'Service parts → Filters; Wash supplies → Shampoo.', 'master-data.taxonomy', ['section' => 'inventory_categories'], 'view_settings', InventoryCategory::where('vendor_id', $vendorId)->where('is_active', true)->count(), false),
            $item('inventory-types', 'Inventory types', 'A narrower group within an inventory category.', 'Filters → Oil filters or Air filters.', 'master-data.taxonomy', ['section' => 'inventory_types'], 'view_settings', InventoryType::whereHas('category', fn ($q) => $q->where('vendor_id', $vendorId))->count(), false),
            $item('inventory-items', 'Inventory items', 'The parts, supplies and tools used or sold by your business.', 'An oil filter with its SKU, unit and reorder level.', 'inventory.index', [], 'view_inventory', InventoryItem::where('vendor_id', $vendorId)->where('is_active', true)->count(), false),
            $item('suppliers', 'Supplier contacts', 'Businesses you buy parts or supplies from; separate from marketplace seller access.', 'Your local parts wholesaler and payment terms.', 'suppliers.index', [], 'view_suppliers', Supplier::where('vendor_id', $vendorId)->where('is_active', true)->count(), false),
            $item('staff', 'Staff and access', 'Your employees, login roles and branch assignments.', 'A technician assigned to the main workshop.', 'users.index', [], 'view_users', User::where('vendor_id', $vendorId)->where('is_active', true)->whereKeyNot($user->id)->count(), false),
            $item('commissions', 'Commission rules', 'How eligible work earns staff commission.', 'An approved labour percentage for technicians.', 'commissions.index', [], 'view_commissions', CommissionRule::where('vendor_id', $vendorId)->where('is_active', true)->count(), false),
        ];

        return array_map(fn ($row) => $row + ['url' => route($row['route'], $row['parameters']), 'canOpen' => $user->can($row['permission'])], $items);
    }

    public function missing(User $user): array
    {
        return array_values(array_filter($this->items($user), fn ($item) => $item['required'] && ! $item['ready']));
    }
}
