<?php

namespace Tests\Feature\Auth;

use App\Domains\HR\Livewire\Auth\VendorRegistration;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Setting;
use App\Domains\Platform\Models\PlatformSetting;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Services\ApiIntegrationService;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('vendor-owner', 'web');
        app(ApiIntegrationService::class)->upsertIntegration('onepass', ['base_url' => 'https://onepass.test', 'api_key' => 'test-key'], true);
        Http::preventStrayRequests();
        Http::fake(['https://onepass.test/*' => Http::response(['message' => 'OK'], 200)]);
    }

    private function plan(array $attributes = []): PricingPlan
    {
        return PricingPlan::create(array_merge([
            'name' => 'Starter', 'slug' => 'starter', 'is_active' => true,
            'is_default' => true, 'billing_model' => 'free', 'has_trial' => true, 'is_trial' => true, 'trial_days' => 14,
            'base_price' => 0, 'currency' => 'UGX', 'billing_cycle' => 'monthly',
        ], $attributes));
    }

    private function wizard(PricingPlan $plan)
    {
        return Livewire::test(VendorRegistration::class)
            ->set('vendor_name', '  Kampala Auto  ')
            ->set('vendor_email', ' GARAGE@EXAMPLE.COM ')
            ->set('vendor_phone', '+256 700 000000')
            ->set('vendor_address', 'Kampala Road')
            ->call('nextStep')->assertSet('currentStep', 2)
            ->set('owner_name', 'Garage Owner')
            ->set('password', 'a-secure-password')
            ->set('password_confirmation', 'a-secure-password')
            ->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')
            ->call('nextStep')->assertSet('currentStep', 3)
            ->set('selectedPlanId', $plan->id)
            ->call('nextStep')->assertSet('currentStep', 4);
    }

    public function test_registration_page_and_empty_plan_state_render(): void
    {
        PricingPlan::query()->update(['is_active' => false]);
        $this->get('/register')->assertOk()->assertSee('Register your garage');
        Livewire::test(VendorRegistration::class)
            ->set('vendor_name', 'Garage')->set('vendor_email', 'garage@example.com')
            ->call('nextStep')->set('owner_name', 'Owner')
            ->set('password', 'password')->set('password_confirmation', 'password')
            ->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')
            ->call('nextStep')->assertSee('There are no plans available')
            ->call('nextStep')->assertHasErrors('selectedPlanId');
    }

    public function test_free_registration_creates_complete_garage_and_logs_in_owner(): void
    {
        $wizard = $this->wizard($this->plan());
        $wizard->assertSee('Due now: UGX 0.00')->call('register')
            ->assertHasNoErrors()->assertRedirect(route('dashboard'));
        $owner = User::where('email', 'garage@example.com')->firstOrFail();
        $vendor = $owner->vendor;
        $branch = $owner->primaryBranch();
        $this->assertAuthenticatedAs($owner);
        $this->assertTrue($owner->hasRole('vendor-owner'));
        $this->assertTrue(Hash::check('a-secure-password', $owner->password));
        $this->assertSame('Kampala Auto', $vendor->name);
        $this->assertSame('Main Branch', $branch->name);
        $this->assertSame('garage@example.com', $branch->email);
        $this->assertSame('Kampala Road', $branch->address);
        $this->assertSame($vendor->id, $branch->vendor_id);
        $this->assertSame($branch->id, session('current_branch_id'));
        $this->assertDatabaseHas('settings', ['vendor_id' => $vendor->id, 'key' => 'currency', 'value' => 'UGX']);
        $this->assertSame(0, $vendor->activeSubscription->invoices()->count());
        $wizard->call('register')->assertRedirect(route('dashboard'));
        $this->assertSame(1, Vendor::count());
    }

    public function test_custom_branch_and_separate_login_are_preserved(): void
    {
        $plan = $this->plan();
        $this->wizard($plan)->call('goToStep', 1)->set('customizeBranch', true)
            ->set('branch_name', 'Entebbe')->set('branch_address', 'Airport Road')
            ->set('branch_email', 'branch@example.com')->set('branch_phone', '+256 711 000000')
            ->call('nextStep')->set('useBusinessEmail', false)->set('owner_email', 'owner@example.com')
            ->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')
            ->call('nextStep')->call('nextStep')->call('register')->assertHasNoErrors();
        $this->assertDatabaseHas('branches', ['name' => 'Entebbe', 'address' => 'Airport Road', 'email' => 'branch@example.com']);
        $this->assertAuthenticatedAs(User::where('email', 'owner@example.com')->firstOrFail());
    }

    public function test_garages_with_the_same_name_get_different_workspace_slugs(): void
    {
        $existing = Vendor::create(['name' => 'Kampala Auto', 'email' => 'existing@example.com']);
        $this->wizard($this->plan())->call('register')->assertHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertNotSame($existing->slug, auth()->user()->vendor->slug);
        $this->assertSame(2, Vendor::count());
    }

    public function test_completed_registration_shows_setup_links_to_the_owner(): void
    {
        foreach (['view_service_templates', 'create_users', 'create_customers'] as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission, 'web');
        }
        Role::findByName('vendor-owner')->givePermissionTo(['view_service_templates', 'create_users', 'create_customers']);
        $this->wizard($this->plan())->call('register');
        $this->blade('<x-garage-setup />')->assertSee('Set up service templates')->assertSee('Invite your staff')->assertSee('Add your first customer');
    }

    public function test_step_state_cannot_be_changed_directly_by_the_client(): void
    {
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        Livewire::test(VendorRegistration::class)->set('furthestStep', 4);
    }

    public function test_trial_without_fees_goes_to_dashboard(): void
    {
        $plan = $this->plan();
        $this->wizard($plan)->assertSee('No card is required')->call('register')->assertRedirect(route('dashboard'));
        $this->assertTrue(auth()->user()->vendor->activeSubscription->isTrialing());
        $this->assertSame(0, auth()->user()->vendor->activeSubscription->invoices()->count());
    }

    public function test_paid_plan_displays_tax_and_routes_to_its_invoice(): void
    {
        PlatformSetting::set(PlatformSetting::PLATFORM_TAX_RATE, 18, 'float');
        $plan = $this->plan(['billing_model' => 'subscription', 'base_price' => 100000, 'is_trial' => false]);
        $this->wizard($plan)->assertSee('Due now: UGX 118,000.00')->call('register')
            ->assertRedirect(route('billing.subscription'));
        $this->assertDatabaseHas('vendor_platform_invoices', ['balance_due' => 118000]);
    }

    public function test_paid_plan_with_setup_fee_discloses_full_amount_and_waits_for_payment(): void
    {
        $plan = $this->plan(['billing_model' => 'subscription', 'base_price' => 100000, 'is_trial' => false, 'setup_fee' => 50000]);
        $this->wizard($plan)->assertSee('Due now: UGX 150,000.00')->assertSee('paid with your first subscription')
            ->call('register')->assertRedirect(route('billing.subscription'));
        $this->assertDatabaseHas('vendor_platform_invoices', ['type' => 'subscription', 'balance_due' => 150000]);
        $this->assertDatabaseHas('vendor_subscriptions', ['status' => 'paused']);
    }

    public function test_navigation_and_validation_cannot_be_bypassed(): void
    {
        Livewire::test(VendorRegistration::class)->call('goToStep', 4)->assertSet('currentStep', 1)
            ->call('goToStep', 0)->assertSet('currentStep', 1)
            ->call('nextStep')->assertHasErrors(['vendor_name', 'vendor_email'])
            ->call('register')->assertHasErrors(['vendor_name', 'vendor_email']);
        $this->assertSame(0, Vendor::count());
    }

    public function test_final_submission_rechecks_edited_details_and_inactive_plan(): void
    {
        $plan = $this->plan();
        $wizard = $this->wizard($plan);
        $plan->update(['is_active' => false]);
        $wizard->call('register')->assertHasErrors('selectedPlanId')->assertSet('currentStep', 3);
        $plan->update(['is_active' => true]);
        $wizard->call('goToStep', 1)->set('vendor_name', '')->call('register')
            ->assertHasErrors('vendor_name')->assertSet('currentStep', 1);
        $this->assertSame(0, Vendor::count());
    }

    public function test_unsupported_plans_cannot_be_selected_or_submitted(): void
    {
        PricingPlan::query()->update(['is_active' => false]);
        $plan = $this->plan(['is_trial' => false, 'billing_model' => 'custom']);
        Livewire::test(VendorRegistration::class)->assertSet('selectedPlanId', null)
            ->call('selectPlan', $plan->id)->assertHasErrors('selectedPlanId');
    }

    public function test_existing_login_email_is_rejected_early(): void
    {
        User::factory()->create(['email' => 'garage@example.com']);
        Livewire::test(VendorRegistration::class)
            ->set('vendor_name', 'Garage')->set('vendor_email', 'garage@example.com')->call('nextStep')
            ->set('owner_name', 'Owner')->set('password', 'password')->set('password_confirmation', 'password')
            ->call('nextStep')->assertHasErrors('owner_email')->assertSet('currentStep', 2);
    }

    public function test_failure_rolls_back_every_registration_record(): void
    {
        $wizard = $this->wizard($this->plan());
        $this->partialMock(SelfServiceSubscriptionService::class)->shouldReceive('start')->once()->andThrow(new \RuntimeException('Billing unavailable'));
        $wizard->call('register')->assertHasErrors('registration')->assertSet('vendor_name', 'Kampala Auto');
        $this->assertGuest();
        $this->assertSame(0, Vendor::count());
        $this->assertSame(0, Branch::count());
        $this->assertSame(0, User::count());
        $this->assertSame(0, Setting::count());
    }

    public function test_registration_attempts_are_rate_limited(): void
    {
        $wizard = Livewire::test(VendorRegistration::class);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $wizard->call('register');
        }
        $wizard->call('register')->assertHasErrors('registration')->assertSee('Too many registration attempts');
    }

    public function test_concurrent_registration_is_rejected_without_creating_records(): void
    {
        $wizard = $this->wizard($this->plan());
        $lock = Cache::lock('garage-registration:'.hash('sha256', 'garage@example.com'), 30);
        $lock->get();
        try {
            $wizard->call('register')->assertHasErrors('registration')->assertSee('already being processed');
            $this->assertSame(0, Vendor::count());
        } finally {
            $lock->release();
        }
    }
}
