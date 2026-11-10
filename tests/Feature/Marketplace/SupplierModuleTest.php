<?php

namespace Tests\Feature\Marketplace;

use App\Domains\Marketplace\Livewire\Supplier\EnableModule;
use App\Domains\Marketplace\Livewire\Supplier\Team;
use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\MarketplaceListing;
use App\Domains\Marketplace\Services\SupplierModuleService;
use App\Domains\Marketplace\Services\SupplierPlanAccess;
use App\Domains\Organization\Models\Branch;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorSubscription;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use App\Models\User;
use Database\Seeders\MarketplaceRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierModuleTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $garage;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Role::findOrCreate('vendor-owner', 'web');
        Role::findOrCreate('branch-manager', 'web');
        $this->seed(MarketplaceRolesSeeder::class);
        Notification::fake();
        $this->garage = Vendor::create(['name' => 'Garage with parts', 'email' => 'garage@example.test', 'vendor_type' => 'garage', 'status' => 'active']);
        $this->owner = User::factory()->create(['vendor_id' => $this->garage->id, 'is_active' => true]);
        $this->owner->assignRole('vendor-owner');
    }

    private function requestModule(): void
    {
        $this->actingAs($this->owner);
        Livewire::test(EnableModule::class)->set('notes', 'We supply filters, brake pads and engine oil.')->set('consent', true)->call('enable')->assertHasNoErrors();
        $this->garage->refresh();
        $this->owner->refresh();
    }

    public function test_catalog_links_offer_onboarding_before_permission_middleware_rejects(): void
    {
        $this->actingAs($this->owner)->get(route('supplier.catalog.index'))->assertRedirect(route('supplier.enable'));
        $this->get(route('supplier.catalog.create'))->assertRedirect(route('supplier.enable'));
        $this->get(route('supplier.enable'))->assertOk()->assertSee('Your garage can sell parts too.')->assertSee('Request supplier access');
        $this->get(route('modules.index'))->assertOk()->assertSee('Add supplier module');
        $this->getJson(route('supplier.catalog.index'))->assertForbidden()->assertJsonPath('supplier_module_url', route('supplier.enable'));
    }

    public function test_owner_adds_supplier_role_once_without_replacing_the_garage_or_subscription(): void
    {
        $branch = Branch::create(['vendor_id' => $this->garage->id, 'name' => 'Main', 'code' => 'MAIN', 'is_active' => true]);
        $subscription = app(SelfServiceSubscriptionService::class)->start($this->garage, PricingPlan::where('slug', 'garagehq-14-day-trial')->firstOrFail());
        $snapshot = $subscription->fresh()->getAttributes();
        $this->requestModule();
        $at = $this->garage->supplier_requested_at->toDateTimeString();
        $this->assertSame('hybrid', $this->garage->vendor_type);
        $this->assertFalse($this->garage->is_verified_supplier);
        $this->assertTrue($this->owner->hasAllRoles(['vendor-owner', 'supplier_admin']));
        $this->assertSame($snapshot, $subscription->fresh()->getAttributes());
        $this->assertSame($this->garage->id, $branch->fresh()->vendor_id);
        $this->assertSame(1, Vendor::count());
        $this->assertSame(1, VendorSubscription::count());
        $this->travel(1)->days();
        app(SupplierModuleService::class)->request($this->owner, 'Different details must not replace the request.');
        $this->assertSame($at, $this->garage->fresh()->supplier_requested_at->toDateTimeString());
        $this->assertSame('We supply filters, brake pads and engine oil.', $this->garage->fresh()->supplier_application_notes);
        $this->get(route('supplier.catalog.index'))->assertOk();
        $this->get(route('supplier.dashboard'))->assertOk();
        $this->get(route('supplier.catalog.create'))->assertForbidden();
    }

    public function test_request_requires_the_owner_consent_and_business_details(): void
    {
        $this->actingAs($this->owner);
        Livewire::test(EnableModule::class)->call('enable')->assertHasErrors(['notes', 'consent']);
        $staff = User::factory()->create(['vendor_id' => $this->garage->id]);
        $staff->assignRole('branch-manager');
        $this->actingAs($staff)->get(route('supplier.enable'))->assertOk()->assertSee('Ask your business owner')->assertDontSee('Request supplier access');
        Livewire::test(EnableModule::class)->set('notes', 'We sell brake pads.')->set('consent', true)->call('enable')->assertForbidden();
        $this->assertSame('garage', $this->garage->fresh()->vendor_type);
        $this->assertFalse($staff->fresh()->hasRole('supplier_admin'));
    }

    public function test_tampered_vendor_context_cannot_enable_another_business(): void
    {
        $other = Vendor::create(['name' => 'Other garage', 'email' => 'other@example.test']);
        session(['current_vendor_id' => $other->id]);
        $this->requestModule();
        $this->assertSame('garage', $other->fresh()->vendor_type);
        $this->assertNull($other->fresh()->supplier_requested_at);
    }

    public function test_inactive_platform_and_suspended_accounts_cannot_apply(): void
    {
        $this->owner->update(['is_active' => false]);
        $this->actingAs($this->owner)->get(route('supplier.enable'))->assertForbidden();
        $this->owner->update(['is_active' => true, 'is_platform_user' => true]);
        Livewire::test(EnableModule::class)->assertForbidden();
        $this->owner->update(['is_platform_user' => false]);
        $this->garage->update(['status' => 'suspended']);
        Livewire::test(EnableModule::class)->set('notes', 'We supply garage parts.')->set('consent', true)->call('enable')->assertForbidden();
        $this->assertFalse($this->owner->fresh()->hasRole('supplier_admin'));
    }

    public function test_platform_approval_unlocks_selling_and_revocation_pauses_it(): void
    {
        $this->requestModule();
        $this->assertFalse(app(SupplierPlanAccess::class)->canSell($this->garage));
        $this->garage->update(['is_verified_supplier' => true]);
        $this->assertNotNull($this->garage->fresh()->supplier_reviewed_at);
        $this->assertTrue(app(SupplierPlanAccess::class)->canSell($this->garage->fresh()));
        $this->get(route('supplier.catalog.create'))->assertOk();
        $this->garage->update(['is_verified_supplier' => false]);
        $this->get(route('supplier.enable'))->assertOk()->assertSee('Supplier access paused');
        $this->get(route('supplier.catalog.create'))->assertForbidden();
        $this->assertTrue($this->owner->fresh()->hasRole('vendor-owner'));
    }

    public function test_existing_garage_staff_can_gain_and_lose_supplier_access_without_losing_their_login(): void
    {
        $this->requestModule();
        $this->garage->update(['is_verified_supplier' => true]);
        $this->actingAs($this->owner->fresh());
        $staff = User::factory()->create(['vendor_id' => $this->garage->id, 'is_active' => true]);
        $staff->assignRole('branch-manager');
        Livewire::test(Team::class)->call('grantSupplierAccess', $staff->id)->assertHasNoErrors();
        $this->assertTrue($staff->fresh()->hasAllRoles(['branch-manager', 'supplier_staff']));
        $this->actingAs($staff->fresh())->get(route('supplier.catalog.index'))->assertOk();
        $this->get(route('supplier.team'))->assertForbidden();
        $this->actingAs($this->owner->fresh());
        Livewire::test(Team::class)->call('removeMember', $staff->id)->assertHasNoErrors();
        $this->assertTrue($staff->fresh()->is_active);
        $this->assertTrue($staff->fresh()->hasRole('branch-manager'));
        $this->assertFalse($staff->fresh()->hasRole('supplier_staff'));
        $this->actingAs($staff->fresh())->get(route('supplier.catalog.index'))->assertRedirect(route('supplier.enable'));
    }

    public function test_supplier_module_never_restarts_the_garage_trial_or_bypasses_its_expiry(): void
    {
        $subscription = app(SelfServiceSubscriptionService::class)->start($this->garage, PricingPlan::where('slug', 'garagehq-14-day-trial')->firstOrFail());
        $this->requestModule();
        $this->garage->update(['is_verified_supplier' => true]);
        $product = CatalogProduct::create(['name' => 'Filter', 'slug' => 'filter', 'is_active' => true, 'is_verified' => true]);
        $listing = MarketplaceListing::create(['supplier_vendor_id' => $this->garage->id, 'catalog_product_id' => $product->id, 'price' => 10000, 'stock_qty' => 4, 'min_order_qty' => 1, 'is_active' => true, 'currency' => 'UGX']);
        $this->assertTrue(MarketplaceListing::browsable()->whereKey($listing->id)->exists());
        $this->travelTo($subscription->trial_ends_at);
        $this->assertFalse(app(SupplierPlanAccess::class)->canSell($this->garage->fresh()));
        $this->assertFalse(MarketplaceListing::browsable()->whereKey($listing->id)->exists());
        $this->get(route('supplier.catalog.create'))->assertRedirect(route('billing.subscription'));
        $this->assertSame(1, VendorSubscription::count());
    }

    public function test_supplier_team_cannot_grant_access_to_a_foreign_user(): void
    {
        $this->requestModule();
        $this->garage->update(['is_verified_supplier' => true]);
        $this->actingAs($this->owner->fresh());
        $other = User::factory()->create();
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(Team::class)->call('grantSupplierAccess', $other->id);
    }

    public function test_existing_verified_hybrid_owner_can_activate_the_workspace_without_losing_approval(): void
    {
        $this->garage->update(['vendor_type' => 'hybrid', 'is_verified_supplier' => true]);
        $this->actingAs($this->owner->fresh());
        Livewire::test(EnableModule::class)->assertSee('Activate supplier workspace')->call('activate')->assertHasNoErrors();
        $this->assertTrue($this->garage->fresh()->isVerifiedSupplier());
        $this->assertTrue($this->owner->fresh()->hasAllRoles(['vendor-owner', 'supplier_admin']));
        $this->assertSame(0, VendorSubscription::count());
    }

    public function test_request_notifies_platform_reviewers_and_approval_notifies_the_owner(): void
    {
        $reviewer = User::factory()->create(['is_platform_user' => true, 'is_active' => true]);
        $reviewer->givePermissionTo('verify_catalog_products');
        $this->requestModule();
        Notification::assertSentTo($reviewer, \App\Domains\Marketplace\Notifications\SupplierModuleNotice::class, fn ($notice) => $notice->event === 'requested');
        $this->garage->update(['is_verified_supplier' => true]);
        Notification::assertSentTo($this->owner, \App\Domains\Marketplace\Notifications\SupplierModuleNotice::class, fn ($notice) => $notice->event === 'approved');
        $this->actingAs($this->owner->fresh())->withSession(['current_vendor_id' => 999999])->get(route('supplier.catalog.index'))->assertOk()->assertSessionHas('current_vendor_id', $this->garage->id);
    }
}
