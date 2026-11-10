<?php

namespace Tests\Feature;

use App\Domains\Inventory\Models\InventoryCategory;
use App\Domains\Inventory\Models\InventoryType;
use App\Domains\Operations\Models\ServiceBay;
use App\Domains\Organization\Livewire\Settings\TaxonomyComponent;
use App\Domains\Organization\Models\Branch;
use App\Domains\Platform\Models\Vendor;
use App\Domains\ServiceConfig\Livewire\Packages\PackagesComponent;
use App\Domains\ServiceConfig\Livewire\Templates\TemplatesComponent;
use App\Domains\ServiceConfig\Models\QualityCheckTemplate;
use App\Domains\ServiceConfig\Models\ServiceCategory;
use App\Domains\ServiceConfig\Models\ServiceTemplate;
use App\Domains\ServiceConfig\Models\ServiceType;
use App\Domains\ServiceConfig\Models\WashPackage;
use App\Models\User;
use App\Shared\Navigation\ModuleRegistry;
use App\Support\MasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->vendor = Vendor::create(['name' => 'Master garage', 'email' => 'master@example.test', 'phone' => '+256700000000', 'address' => 'Main street', 'vendor_type' => 'garage', 'status' => 'active']);
        $this->branch = Branch::create(['vendor_id' => $this->vendor->id, 'name' => 'Main', 'is_active' => true]);
        $role = Role::findOrCreate('vendor-owner', 'web');
        foreach (['view_service_templates', 'view_settings', 'view_branches', 'manage_bays', 'view_users', 'view_suppliers', 'view_commissions', 'manage_roles'] as $name) {
            $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }
        $this->owner = User::factory()->create(['vendor_id' => $this->vendor->id, 'is_active' => true]);
        $this->owner->assignRole($role);
        $this->actingAs($this->owner)->withSession(['current_branch_id' => $this->branch->id]);
    }

    private function checklist(): array
    {
        return array_column(app(MasterData::class)->items($this->owner), null, 'id');
    }

    public function test_start_here_uses_saved_records_without_the_registration_session_flag(): void
    {
        session()->forget('garage_setup');
        $this->assertStringContainsString('Start here:', Blade::render('<x-garage-setup />'));
        $this->assertFalse($this->checklist()['service-templates']['ready']);
        ServiceCategory::create(['name' => 'Maintenance', 'is_active' => true]);
        ServiceType::create(['name' => 'Oil service', 'price' => 20000, 'estimated_duration' => 45]);
        $template = ServiceTemplate::create(['vendor_id' => $this->vendor->id, 'name' => 'Oil service', 'category' => 'service', 'is_active' => true]);
        $template->items()->create(['item_type' => 'labor', 'description' => 'Replace oil', 'quantity' => 1, 'unit_price' => 20000]);
        QualityCheckTemplate::create(['vendor_id' => $this->vendor->id, 'section' => 'exterior', 'item_name' => 'Lights working', 'is_active' => true]);
        ServiceBay::create(['branch_id' => $this->branch->id, 'name' => 'Bay 1', 'bay_type' => 'general', 'status' => 'available']);
        $this->assertSame([], app(MasterData::class)->missing($this->owner));
        $this->assertStringNotContainsString('Start here:', Blade::render('<x-garage-setup />'));
        $template->update(['is_active' => false]);
        $this->assertStringContainsString('Set up service templates', Blade::render('<x-garage-setup />'));
    }

    public function test_another_business_cannot_complete_this_garages_setup(): void
    {
        $other = Vendor::create(['name' => 'Other', 'email' => 'other-master@example.test']);
        $otherBranch = Branch::create(['vendor_id' => $other->id, 'name' => 'Other branch', 'is_active' => true]);
        ServiceTemplate::create(['vendor_id' => $other->id, 'name' => 'Private template', 'is_active' => true]);
        ServiceBay::create(['branch_id' => $otherBranch->id, 'name' => 'Other bay', 'status' => 'available']);
        session(['current_branch_id' => $otherBranch->id]);
        $this->assertFalse($this->checklist()['service-templates']['ready']);
        $this->assertFalse($this->checklist()['service-bays']['ready']);
    }

    public function test_car_wash_setup_requires_wash_records_without_requiring_repair_templates(): void
    {
        $this->vendor->update(['vendor_type' => 'car_wash']);
        $this->owner->unsetRelation('vendor');
        $items = $this->checklist();
        $this->assertTrue($items['wash-packages']['required']);
        $this->assertTrue($items['wash-bays']['required']);
        $this->assertFalse($items['service-templates']['required']);
        $this->assertFalse($items['service-bays']['required']);
    }

    public function test_defaults_are_available_to_real_quality_inspections_without_leaking_other_vendors(): void
    {
        $system = QualityCheckTemplate::withoutEvents(fn () => QualityCheckTemplate::create(['vendor_id' => null, 'section' => 'exterior', 'item_name' => 'System light check', 'is_active' => true, 'is_default' => true]));
        $other = Vendor::create(['name' => 'Other inspector', 'email' => 'other-inspector@example.test']);
        QualityCheckTemplate::create(['vendor_id' => $other->id, 'section' => 'exterior', 'item_name' => 'Other private check', 'is_active' => true]);
        $items = QualityCheckTemplate::getForVendor($this->vendor->id)->flatten();
        $this->assertTrue($items->contains('id', $system->id));
        $this->assertFalse($items->contains('item_name', 'Other private check'));
        $this->assertTrue($this->checklist()['quality-checklists']['ready']);
    }

    public function test_master_data_lists_real_destinations_and_respects_action_permissions(): void
    {
        $this->get(route('master-data.index'))->assertOk()->assertSee('Master data setup directory')->assertSee('Inventory categories')->assertSee('Quality checklist items');
        foreach (app(MasterData::class)->items($this->owner) as $item) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($item['route']));
        }
        $module = ModuleRegistry::current('templates.index');
        $this->assertSame('Master data', $module['name']);
        $this->assertSame('Master data', ModuleRegistry::current('packages.index')['name']);
        $this->assertContains('Quality checklist', array_column($module['items'], 'label'));
        $this->owner->syncRoles([]);
        $this->assertFalse($this->checklist()['service-templates']['canOpen']);
    }

    public function test_templates_save_the_group_and_validated_lines_used_by_work_orders(): void
    {
        Livewire::test(TemplatesComponent::class)->set('serviceName', 'Brake service')->set('serviceType', 'repair')
            ->set('serviceItems', [['item_type' => 'labor', 'description' => 'Inspect and service brakes', 'quantity' => 2, 'unit_price' => 20000]])->call('createServiceTemplate')->assertHasNoErrors();
        $template = ServiceTemplate::where('name', 'Brake service')->firstOrFail();
        $this->assertSame('repair', $template->category);
        $this->assertSame(40000.0, $template->total_price);
        Livewire::test(TemplatesComponent::class)->set('serviceName', 'Incomplete')->set('serviceItems', [['item_type' => 'labor', 'description' => '', 'quantity' => -1, 'unit_price' => 0]])->call('createServiceTemplate')->assertHasErrors();
        $this->assertFalse(ServiceTemplate::where('name', 'Incomplete')->exists());
    }

    public function test_both_package_screens_share_saved_inclusions_and_valid_types(): void
    {
        Livewire::test(TemplatesComponent::class)->set('tab', 'wash')->set('washName', 'Standard wash')->set('washType', 'standard')->set('washPrice', 25000)->set('washServices', [['name' => 'Body wash'], ['name' => 'Vacuum']])->call('createWashPackage')->assertHasNoErrors();
        $package = WashPackage::where('name', 'Standard wash')->firstOrFail();
        $this->assertSame(['Body wash', 'Vacuum'], $package->includes_list);
        Livewire::test(\App\Domains\Operations\Livewire\WashOrders\CreateWashOrdersComponent::class)->set('wash_package_id', $package->id)->assertSet('total', 25000.0)->assertSet('items.0.price', 25000.0)->assertSet('items.1.price', 0);
        Livewire::test(PackagesComponent::class)->set('filterType', 'standard')->assertSee('Standard wash')->assertSee('Body wash')->assertSee('Wash package price list');
        $this->get('/templates?tab=wash')->assertOk()->assertSee('Wash packages: a clear bundle');
        $this->get('/templates?tab=quality')->assertOk()->assertSee('Quality checklists: consistent checks');
        $this->get('/templates?tab=unknown')->assertOk()->assertSee('Service templates: reusable recipes');
    }

    public function test_inventory_types_remain_scoped_to_their_business_category(): void
    {
        $other = Vendor::create(['name' => 'Other stock', 'email' => 'other-stock@example.test']);
        $category = InventoryCategory::create(['vendor_id' => $other->id, 'name' => 'Foreign category', 'type' => 'service_parts', 'is_active' => true]);
        InventoryType::create(['inventory_category_id' => $category->id, 'name' => 'Private foreign type']);
        Livewire::test(TaxonomyComponent::class)->set('section', 'inventory_types')->assertDontSee('Private foreign type')
            ->set('inventoryTypeName', 'Invalid reference')->set('inventoryTypeCategoryId', $category->id)->call('saveInventoryType')->assertHasErrors('inventoryTypeCategoryId');
    }
}
