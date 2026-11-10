<?php

namespace Tests\Feature\Marketplace;

use App\Domains\HR\Livewire\Auth\Login;
use App\Domains\Marketplace\Livewire\Supplier\Registration\Register;
use App\Domains\Marketplace\Notifications\SupplierSignupReceived;
use App\Domains\Marketplace\Notifications\SupplierWelcomeEmailChannel;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Setting;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorSubscription;
use App\Domains\Platform\Services\ApiIntegrationService;
use App\Models\User;
use Database\Seeders\MarketplaceRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceRolesSeeder::class);
        Notification::fake();
        Http::preventStrayRequests();
        app(ApiIntegrationService::class)->upsertIntegration('onepass', ['base_url' => 'https://onepass.test', 'api_key' => 'supplier-test-key'], true);
        Http::fake(['https://onepass.test/*' => Http::response(['message' => 'OK'])]);
    }

    private function accountStep()
    {
        return Livewire::test(Register::class)->set('business_name', '  Kampala Auto Parts  ')
            ->set('business_email', ' SALES@EXAMPLE.COM ')->set('business_phone', '+256 700 000000')
            ->set('business_address', '  Kampala Road  ')->call('nextStep')->assertSet('currentStep', 2)
            ->set('owner_name', 'Supplier Owner')->set('password', 'a-secure-password')->set('password_confirmation', 'a-secure-password');
    }

    private function review()
    {
        return $this->accountStep()->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')
            ->call('nextStep')->assertSet('currentStep', 3)->call('nextStep')->assertSet('currentStep', 4);
    }

    public function test_supplier_registration_renders_with_supplier_branding(): void
    {
        $this->get(route('supplier.register'))->assertOk()->assertSee('Register as a supplier')->assertSee('Parts Supplier Marketplace')->assertSee('What happens next');
    }

    public function test_verified_supplier_signup_creates_only_supplier_and_owner_pending_approval(): void
    {
        session(['current_branch_id' => 999, 'current_vendor_id' => 999, 'garage_setup' => true]);
        $wizard = $this->review()->assertSee('Verified')->assertSee('approval pending');
        $wizard->call('register')->assertHasNoErrors()->assertRedirect(route('supplier.dashboard'));
        $owner = User::where('email', 'sales@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($owner);
        $this->assertNotNull($owner->email_verified_at);
        $this->assertTrue(Hash::check('a-secure-password', $owner->password));
        $this->assertTrue($owner->hasRole('supplier_admin'));
        $this->assertSame('supplier', $owner->vendor->vendor_type);
        $this->assertSame('Kampala Auto Parts', $owner->vendor->name);
        $this->assertSame('Kampala Road', $owner->vendor->address);
        $this->assertFalse($owner->vendor->is_verified_supplier);
        $this->assertSame(0, Branch::count());
        $this->assertSame(1, VendorSubscription::count());
        $this->assertSame('paused', VendorSubscription::first()->status);
        $this->assertNull(VendorSubscription::first()->trial_ends_at);
        $this->assertSame(0, Setting::count());
        $this->assertNull(session('current_branch_id'));
        $this->assertNull(session('current_vendor_id'));
        Notification::assertSentTo($owner, SupplierSignupReceived::class);
        $wizard->call('register')->assertRedirect(route('supplier.dashboard'));
        $this->assertSame(1, Vendor::count());
        Notification::assertSentToTimes($owner, SupplierSignupReceived::class, 1);
        $this->get(route('supplier.dashboard'))->assertOk()->assertSee('awaiting platform approval');
        $this->get(route('dashboard'))->assertRedirect(route('supplier.dashboard'));
        $this->get(route('supplier.catalog.index'))->assertOk();
    }

    public function test_separate_login_email_is_verified_and_preserved(): void
    {
        $this->accountStep()->set('useBusinessEmail', false)->set('owner_email', ' OWNER@EXAMPLE.COM ')
            ->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')
            ->call('nextStep')->call('register')->assertHasNoErrors();
        $this->assertSame('owner@example.com', auth()->user()->email);
        $this->assertSame('sales@example.com', auth()->user()->vendor->email);
    }

    public function test_email_verification_cannot_be_skipped_or_reused_for_a_changed_address(): void
    {
        $this->accountStep()->call('nextStep')->assertHasErrors('verification')->assertSet('currentStep', 2);
        $wizard = $this->review();
        $wizard->call('goToStep', 2)->set('useBusinessEmail', false)->set('owner_email', 'changed@example.com')
            ->call('register')->assertHasErrors('verification')->assertSet('currentStep', 2);
        $this->assertSame(0, Vendor::count());
    }

    public function test_expired_proof_returns_to_account_step_without_creating_records(): void
    {
        $wizard = $this->review();
        $this->travel(31)->minutes();
        $wizard->call('register')->assertHasErrors('verification')->assertSet('currentStep', 2);
        $this->assertSame(0, User::count());
    }

    public function test_steps_cannot_be_skipped_and_final_submission_revalidates_business(): void
    {
        Livewire::test(Register::class)->call('goToStep', 3)->assertSet('currentStep', 1)
            ->call('goToStep', -1)->assertSet('currentStep', 1)->call('nextStep')->assertHasErrors(['business_name', 'business_email']);
        $this->review()->call('goToStep', 1)->set('business_name', '')->call('register')->assertHasErrors('business_name')->assertSet('currentStep', 1);
        $this->assertSame(0, Vendor::count());
    }

    public function test_step_state_cannot_be_tampered_with(): void
    {
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        Livewire::test(Register::class)->set('furthestStep', 3);
    }

    public function test_duplicate_business_names_get_unique_slugs(): void
    {
        $existing = Vendor::create(['name' => 'Kampala Auto Parts', 'email' => 'existing@example.com']);
        $this->review()->call('register')->assertHasNoErrors();
        $this->assertNotSame($existing->slug, auth()->user()->vendor->slug);
    }

    public function test_existing_login_email_is_rejected_before_verification(): void
    {
        User::factory()->create(['email' => 'sales@example.com']);
        $this->accountStep()->call('sendVerificationCode')->assertHasErrors('owner_email');
        Http::assertNothingSent();
    }

    public function test_concurrent_signup_is_rejected_without_partial_records(): void
    {
        $wizard = $this->review();
        $lock = Cache::lock('garage-registration:'.hash('sha256', 'sales@example.com'), 30);
        $lock->get();
        try {
            $wizard->call('register')->assertHasErrors('registration')->assertSee('already being processed');
            $this->assertSame(0, Vendor::count());
        } finally {
            $lock->release();
        }
    }

    public function test_failure_rolls_back_supplier_and_owner_and_preserves_form(): void
    {
        $wizard = $this->review();
        Role::findByName('supplier_admin')->delete();
        $wizard->call('register')->assertHasErrors('registration')->assertSet('business_name', 'Kampala Auto Parts');
        $this->assertGuest();
        $this->assertSame(0, Vendor::count());
        $this->assertSame(0, User::count());
        Notification::assertNothingSent();
    }

    public function test_registration_is_rate_limited(): void
    {
        $wizard = Livewire::test(Register::class);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $wizard->call('register');
        }
        $wizard->call('register')->assertHasErrors('registration')->assertSee('Too many registration attempts');
    }

    public function test_platform_approval_is_still_required_after_verified_email_signup(): void
    {
        $this->review()->call('register');
        $this->get(route('supplier.catalog.create'))->assertForbidden();
        Livewire::test(\App\Domains\Marketplace\Livewire\Supplier\Listings\Index::class)->call('save')->assertForbidden();
        auth()->user()->vendor->update(['is_verified_supplier' => true]);
        $this->get(route('supplier.catalog.create'))->assertOk();
    }

    public function test_supplier_login_returns_to_supplier_dashboard(): void
    {
        $this->review()->call('register');
        auth()->logout();
        session(['current_branch_id' => 999, 'current_vendor_id' => 999]);
        Livewire::test(Login::class)->set('email', 'sales@example.com')->set('password', 'a-secure-password')
            ->call('login')->assertRedirect(route('supplier.dashboard'));
        $this->assertNull(session('current_branch_id'));
        $this->assertNull(session('current_vendor_id'));
    }

    public function test_welcome_email_uses_cream_theme_and_explains_approval(): void
    {
        app(ApiIntegrationService::class)->upsertIntegration('cream_theme', ['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret'], true);
        Http::fake(['https://cream.test/api/mail/send' => Http::response(['status' => 'sent'], 201)]);
        $owner = User::factory()->create();
        (new SupplierWelcomeEmailChannel)->send($owner, new SupplierSignupReceived);
        Http::assertSent(fn ($request) => $request->url() === 'https://cream.test/api/mail/send' && $request['to'] === $owner->email
            && str_contains($request['text'], 'platform administrator') && ! str_contains($request['text'], 'password'));
        $notification = new SupplierSignupReceived;
        $this->assertSame('database', $notification->connection);
        $this->assertTrue($notification->afterCommit);
    }
}
