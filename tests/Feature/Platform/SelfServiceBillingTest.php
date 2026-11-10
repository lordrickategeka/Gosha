<?php

namespace Tests\Feature\Platform;

use App\Domains\Platform\Livewire\Billing\SubscriptionComponent;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\SubscriptionNotice;
use App\Domains\Platform\Models\SubscriptionPaymentAttempt;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use App\Domains\Platform\Models\VendorSubscription;
use App\Domains\Platform\Services\ApiIntegrationService;
use App\Domains\Platform\Services\CreamThemePaymentService;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use App\Jobs\SendSubscriptionNotice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Livewire\Component;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SelfServiceBillingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Vendor $vendor;

    private VendorSubscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Role::findOrCreate('vendor-owner', 'web');
        $this->vendor = Vendor::create(['name' => 'Test garage', 'email' => 'business@example.com']);
        $this->owner = User::factory()->create(['vendor_id' => $this->vendor->id, 'email_verified_at' => now()]);
        $this->owner->assignRole('vendor-owner');
        $this->subscription = app(SelfServiceSubscriptionService::class)->start($this->vendor, PricingPlan::where('is_trial', true)->firstOrFail());
        app(ApiIntegrationService::class)->upsertIntegration('cream_theme', [
            'base_url' => 'https://cream.test', 'api_key' => 'partner', 'api_secret' => 'secret', 'environment' => 'test',
        ], true);
    }

    private function paidPlan(array $attributes = []): PricingPlan
    {
        return PricingPlan::create(array_merge(['name' => 'Growth', 'slug' => 'growth', 'is_active' => true,
            'billing_model' => 'subscription', 'base_price' => 100000, 'currency' => 'UGX', 'billing_cycle' => 'monthly',
            'has_trial' => true, 'trial_days' => 14], $attributes));
    }

    private function invoice(): VendorPlatformInvoice
    {
        return app(SelfServiceSubscriptionService::class)->purchaseInvoice($this->subscription, $this->paidPlan());
    }

    private function attempt(VendorPlatformInvoice $invoice): SubscriptionPaymentAttempt
    {
        return SubscriptionPaymentAttempt::create(['invoice_id' => $invoice->id, 'reference' => 'ghq-test', 'tx_ref' => 'pay_test',
            'environment' => 'test', 'status' => 'pending', 'amount' => $invoice->balance_due, 'currency' => 'UGX']);
    }

    private function verificationResponse(SubscriptionPaymentAttempt $attempt, array $overrides = []): array
    {
        return array_merge(['txRef' => $attempt->tx_ref, 'reference' => $attempt->reference,
            'environment' => 'test', 'amount' => (float) $attempt->amount, 'currency' => 'UGX', 'status' => 'successful'], $overrides);
    }

    public function test_trial_is_zero_cost_and_expires_at_exact_boundary_without_a_scheduler(): void
    {
        $this->assertFalse($this->subscription->auto_renew);
        $this->assertSame(0, $this->subscription->invoices()->count());
        $this->actingAs($this->owner);
        $this->travelTo($this->subscription->trial_ends_at->copy()->subSecond());
        $this->assertFalse(app(SelfServiceSubscriptionService::class)->restricted($this->subscription));
        $this->travelTo($this->subscription->trial_ends_at);
        $this->get('/dashboard')->assertRedirect(route('billing.subscription'));
        $this->assertSame(0, $this->subscription->invoices()->count());
    }

    public function test_expiry_and_reminder_notices_are_deduplicated(): void
    {
        $service = app(SelfServiceSubscriptionService::class);
        $this->travelTo($this->subscription->trial_ends_at->copy()->subDays(3));
        $service->tick();
        $service->tick();
        $this->assertSame(1, SubscriptionNotice::where('event', 'trial-reminder-3')->count());
        $this->travelTo($this->subscription->trial_ends_at->copy()->subDay());
        $service->tick();
        $this->assertSame(1, SubscriptionNotice::where('event', 'trial-reminder-1')->count());
        $this->travelTo($this->subscription->trial_ends_at);
        $service->tick();
        $service->tick();
        $this->assertSame('expired', $this->subscription->fresh()->status);
        $this->assertSame(3, SubscriptionNotice::count());
        $this->assertSame(3, $this->owner->notifications()->count());
        $this->assertSame(0, $this->subscription->invoices()->count());
    }

    public function test_expired_stale_livewire_component_cannot_execute_actions(): void
    {
        $this->actingAs($this->owner);
        $component = Livewire::test(OperationalBillingProbe::class);
        $this->travelTo($this->subscription->trial_ends_at);
        $component->call('mutate')->assertForbidden();
        $this->assertSame(0, OperationalBillingProbe::$writes);
    }

    public function test_expired_accounts_can_choose_a_paid_plan_in_billing_but_cannot_restart_trial(): void
    {
        $this->travelTo($this->subscription->trial_ends_at);
        $plan = $this->paidPlan();
        $this->actingAs($this->owner);
        Livewire::test(SubscriptionComponent::class)->assertSee('Your garage data is safe')
            ->call('changePlan', $plan->id)->assertHasNoErrors()
            ->call('changePlan', $plan->id)->assertHasNoErrors();
        $this->assertSame(1, $this->subscription->invoices()->count());
        $this->assertSame('trial', $this->subscription->fresh()->status);
        Livewire::test(SubscriptionComponent::class)->call('changePlan', $this->subscription->pricing_plan_id)->assertHasErrors('plan');
    }

    public function test_expired_accounts_are_blocked_from_operational_api_routes(): void
    {
        $this->travelTo($this->subscription->trial_ends_at);
        Sanctum::actingAs($this->owner);
        $this->getJson('/api/job-cards/search-customers')->assertForbidden();
    }

    public function test_plan_limits_are_enforced_for_new_users(): void
    {
        $this->subscription->plan->update(['max_users' => 1]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        User::factory()->create(['vendor_id' => $this->vendor->id]);
    }

    public function test_checkout_reuses_a_reference_and_sends_partner_credentials_only_to_backend(): void
    {
        $invoice = $this->invoice();
        Http::fake(function ($request) {
            $this->assertTrue($request->hasHeader('X-Api-Key', 'partner'));
            $this->assertTrue($request->hasHeader('X-Api-Secret', 'secret'));

            return Http::response(['txRef' => 'pay_test', 'reference' => $request['reference'], 'environment' => 'test',
                'status' => 'pending', 'amount' => $request['amount'], 'currency' => $request['currency'],
                'checkoutUrl' => 'https://checkout.flutterwave.com/test'], 201);
        });
        $payments = app(CreamThemePaymentService::class);
        $this->assertSame('https://checkout.flutterwave.com/test', $payments->checkout($invoice, $this->owner));
        $payments->checkout($invoice, $this->owner);
        $this->assertSame(1, SubscriptionPaymentAttempt::count());
        $this->assertSame('trial', $this->subscription->fresh()->status);
    }

    public function test_confirmed_payment_extends_trial_once_and_records_one_receipt(): void
    {
        $invoice = $this->invoice();
        $attempt = $this->attempt($invoice);
        Http::fake(['https://cream.test/*' => Http::response($this->verificationResponse($attempt))]);
        $service = app(CreamThemePaymentService::class);
        $this->assertTrue($service->reconcile($attempt));
        $end = $this->subscription->fresh()->current_period_end->toDateTimeString();
        $this->assertTrue($service->reconcile($attempt->fresh()));
        $this->assertSame($end, $this->subscription->fresh()->current_period_end->toDateTimeString());
        $this->assertSame($this->subscription->trial_ends_at->copy()->addDays(30)->toDateTimeString(), $end);
        $this->assertSame('active', $this->subscription->fresh()->status);
        $this->assertTrue($invoice->fresh()->isPaid());
        $this->assertSame(1, $invoice->payments()->count());
        $this->assertSame(1, SubscriptionNotice::count());
    }

    public function test_payment_after_trial_expiry_starts_paid_period_from_confirmation(): void
    {
        $invoice = $this->invoice();
        $attempt = $this->attempt($invoice);
        $this->travelTo($this->subscription->trial_ends_at->copy()->addDays(2));
        Http::fake(['https://cream.test/*' => Http::response($this->verificationResponse($attempt))]);
        $this->assertTrue(app(CreamThemePaymentService::class)->reconcile($attempt));
        $this->assertSame(now()->addDays(30)->toDateTimeString(), $this->subscription->fresh()->current_period_end->toDateTimeString());
    }

    public function test_wrong_amount_currency_environment_or_reference_cannot_activate_subscription(): void
    {
        $attempt = $this->attempt($this->invoice());
        $overrides = [['amount' => 1], ['currency' => 'USD'], ['environment' => 'live'], ['reference' => 'foreign'], ['txRef' => 'foreign'], ['status' => 'pending']];
        $sequence = Http::sequence();
        foreach ($overrides as $override) {
            $sequence->push($this->verificationResponse($attempt, $override));
        }
        Http::fake(['https://cream.test/*' => $sequence]);
        foreach ($overrides as $override) {
            $this->assertFalse(app(CreamThemePaymentService::class)->reconcile($attempt->fresh()));
        }
        $this->assertSame('trial', $this->subscription->fresh()->status);
        $this->assertSame(0, $attempt->invoice->payments()->count());
    }

    public function test_callback_ignores_claimed_success_and_confirms_through_cream_theme(): void
    {
        $attempt = $this->attempt($this->invoice());
        Http::fake(['https://cream.test/*' => Http::response($this->verificationResponse($attempt, ['status' => 'pending']))]);
        $this->actingAs($this->owner)->get('/billing/pay/callback?status=successful&tx_ref=pay_test&transaction_id=forged')
            ->assertRedirect(route('billing.subscription'))->assertSessionHas('error');
        $this->assertSame('trial', $this->subscription->fresh()->status);
    }

    public function test_another_garage_cannot_pay_or_reconcile_this_invoice(): void
    {
        $attempt = $this->attempt($this->invoice());
        $otherVendor = Vendor::create(['name' => 'Other', 'email' => 'other@example.com']);
        $other = User::factory()->create(['vendor_id' => $otherVendor->id]);
        $other->assignRole('vendor-owner');
        $this->actingAs($other)->post(route('billing.pay', $attempt->invoice))->assertForbidden();
        $this->get('/billing/pay/callback?tx_ref=pay_test')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_background_reconciliation_recovers_payment_without_a_browser_callback(): void
    {
        $attempt = $this->attempt($this->invoice());
        $this->travel(2)->minutes();
        Http::fake(function ($request) use ($attempt) {
            return str_contains($request->url(), '/verify') ? Http::response($this->verificationResponse($attempt)) : Http::response(['status' => 'sent'], 201);
        });
        $this->artisan('billing:self-service')->assertSuccessful();
        $this->assertSame('active', $this->subscription->fresh()->status);
        $this->assertTrue($attempt->fresh()->confirmed_at !== null);
    }

    public function test_email_outbox_retries_failure_and_does_not_resend_sent_messages(): void
    {
        $service = app(SelfServiceSubscriptionService::class);
        $service->notice($this->subscription, 'trial-started', 'Trial started', 'Your free trial ends in 14 days.');
        $notice = SubscriptionNotice::firstOrFail();
        Http::fake(['https://cream.test/*' => Http::sequence()->push(['error' => 'unavailable'], 503)->push(['status' => 'sent'], 201)]);
        try {
            (new SendSubscriptionNotice($notice->id))->handle(app(\App\Domains\Platform\Services\CreamThemeConnector::class));
            $this->fail('Failed delivery must be recorded.');
        } catch (\RuntimeException) {
            $this->assertNull($notice->fresh()->sent_at);
            $this->assertNotNull($notice->fresh()->last_error);
        }
        $this->travel(16)->minutes();
        $job = new SendSubscriptionNotice($notice->id);
        $job->handle(app(\App\Domains\Platform\Services\CreamThemeConnector::class));
        $job->handle(app(\App\Domains\Platform\Services\CreamThemeConnector::class));
        $this->assertNotNull($notice->fresh()->sent_at);
        $this->assertSame(2, $notice->fresh()->attempts);
    }
}

class OperationalBillingProbe extends Component
{
    public static int $writes = 0;

    public function mutate(): void
    {
        self::$writes++;
    }

    public function render()
    {
        return '<div>Operations</div>';
    }
}
