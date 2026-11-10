<?php

namespace Tests\Feature\Marketplace;

use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Livewire\Supplier\Team;
use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\MarketplaceListing;
use App\Domains\Marketplace\Models\PurchaseOrder;
use App\Domains\Marketplace\Services\MarketplaceCommissionService;
use App\Domains\Marketplace\Services\SupplierPlanAccess;
use App\Domains\Platform\Livewire\Billing\SubscriptionComponent;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\SubscriptionNotice;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use App\Domains\Platform\Models\VendorSubscription;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use App\Models\User;
use Database\Seeders\MarketplaceRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierPlansTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;

    private User $owner;

    private VendorSubscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceRolesSeeder::class);
        Notification::fake();
        Http::preventStrayRequests();
        $this->vendor = Vendor::create(['name' => 'Supplier', 'email' => 'supplier@example.com', 'vendor_type' => 'supplier', 'status' => 'active']);
        $this->owner = User::factory()->create(['vendor_id' => $this->vendor->id]);
        $this->owner->assignRole('supplier_admin');
        $this->subscription = app(SelfServiceSubscriptionService::class)->start($this->vendor, PricingPlan::where('slug', 'supplier-trial')->firstOrFail());
    }

    private function approve(): void
    {
        $this->vendor->update(['is_verified_supplier' => true]);
        $this->subscription->refresh();
    }

    private function paid(string $slug = 'supplier-growth'): void
    {
        $this->approve();
        $plan = PricingPlan::where('slug', $slug)->firstOrFail();
        $this->subscription->update(['pricing_plan_id' => $plan->id, 'status' => 'active', 'trial_ends_at' => null, 'current_period_end' => now()->addMonth(), 'last_payment_at' => now()]);
        $this->subscription->refresh();
    }

    private function listing(): MarketplaceListing
    {
        $product = CatalogProduct::create(['name' => 'Brake pads', 'slug' => 'brake-pads-'.uniqid(), 'is_active' => true, 'is_verified' => true]);

        return MarketplaceListing::create(['supplier_vendor_id' => $this->vendor->id, 'catalog_product_id' => $product->id,
            'price' => 10000, 'stock_qty' => 5, 'min_order_qty' => 1, 'is_active' => true, 'currency' => 'UGX']);
    }

    private function order(): PurchaseOrder
    {
        $buyer = Vendor::create(['name' => 'Buyer '.uniqid(), 'email' => uniqid().'@example.com']);
        $po = PurchaseOrder::create(['po_number' => 'PO-'.uniqid(), 'buyer_vendor_id' => $buyer->id, 'supplier_vendor_id' => $this->vendor->id,
            'total' => 100000, 'currency' => 'UGX', 'status' => PurchaseOrderStatus::Draft]);
        $po->update(['status' => PurchaseOrderStatus::Accepted]);

        return $po;
    }

    public function test_supplier_tiers_and_prices_are_seeded_separately(): void
    {
        $plans = PricingPlan::where('audience', 'supplier')->orderBy('sort_order')->get();
        $this->assertSame([0, 30000, 75000, 150000], $plans->pluck('base_price')->map(fn ($price) => (int) $price)->all());
        $this->assertSame([50, 100, 500, 2000], $plans->pluck('max_active_listings')->all());
        $this->assertSame([2, 2, 5, 15], $plans->pluck('max_users')->all());
    }

    public function test_trial_clock_starts_at_approval_and_never_restarts(): void
    {
        $this->assertNull($this->subscription->trial_ends_at);
        $this->travel(7)->days();
        $this->approve();
        $end = $this->subscription->trial_ends_at->toDateTimeString();
        $this->assertSame(now()->addDays(14)->toDateTimeString(), $end);
        $this->assertSame(1, SubscriptionNotice::where('event', 'trial-started')->count());
        $this->travel(2)->days();
        $this->vendor->update(['is_verified_supplier' => false]);
        $this->vendor->update(['is_verified_supplier' => true]);
        $this->assertSame($end, $this->subscription->fresh()->trial_ends_at->toDateTimeString());
        $this->assertSame(0, VendorPlatformInvoice::count());
    }

    public function test_expiry_hides_public_listings_but_keeps_existing_order_access(): void
    {
        $this->approve();
        $listing = $this->listing();
        $po = $this->order();
        $this->assertTrue(MarketplaceListing::browsable()->whereKey($listing->id)->exists());
        $this->travelTo($this->subscription->trial_ends_at);
        $this->assertFalse(app(SupplierPlanAccess::class)->canSell($this->vendor));
        $this->assertFalse(MarketplaceListing::browsable()->whereKey($listing->id)->exists());
        $this->actingAs($this->owner)->get(route('supplier.dashboard'))->assertOk();
        $this->get(route('supplier.orders.fulfill', $po))->assertOk();
        Livewire::test(\App\Domains\Marketplace\Livewire\Supplier\Listings\Index::class)->call('save')->assertForbidden();
        $this->assertTrue($listing->fresh()->is_active); // Data is preserved; the public query pauses visibility.
    }

    public function test_active_listing_limit_is_enforced_and_can_be_recovered_by_deactivation(): void
    {
        $this->approve();
        $this->subscription->plan->update(['max_active_listings' => 1]);
        $listing = $this->listing();
        try {
            $this->listing();
            $this->fail('Listing limit should reject a second active listing.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('plan', $exception->errors());
        }
        $listing->update(['is_active' => false]);
        $this->assertNotNull($this->listing()->id);
    }

    public function test_paid_selection_waits_for_approval_and_verified_payment(): void
    {
        $this->subscription->delete();
        $plan = PricingPlan::where('slug', 'supplier-starter')->firstOrFail();
        $subscription = app(SelfServiceSubscriptionService::class)->start($this->vendor, $plan);
        $this->assertSame(0, VendorPlatformInvoice::count());
        $this->vendor->update(['is_verified_supplier' => true]);
        $this->assertSame('paused', $subscription->fresh()->status);
        $invoice = $subscription->invoices()->firstOrFail();
        $this->assertSame(30000.0, (float) $invoice->balance_due);
        $this->assertFalse(app(SupplierPlanAccess::class)->canSell($this->vendor));
        $invoice->recordPayment(30000, 'other');
        app(SelfServiceSubscriptionService::class)->activatePaidInvoice($invoice);
        $this->assertTrue(app(SupplierPlanAccess::class)->canSell($this->vendor));
    }

    public function test_supplier_billing_owner_can_choose_supplier_plan_but_not_garage_plan(): void
    {
        $this->approve();
        $this->actingAs($this->owner);
        Livewire::test(SubscriptionComponent::class)->call('changePlan', PricingPlan::where('slug', 'supplier-growth')->value('id'))->assertHasNoErrors();
        Livewire::test(SubscriptionComponent::class)->call('changePlan', PricingPlan::where('audience', 'garage')->first()->id)->assertHasErrors('plan');
        $this->assertSame('trial', $this->subscription->fresh()->status);
    }

    public function test_team_seat_limit_and_supplier_only_roles(): void
    {
        $this->approve();
        $this->actingAs($this->owner);
        $component = Livewire::test(Team::class)->set('name', 'Parts clerk')->set('email', 'clerk@example.com')
            ->set('password', 'initial-secret')->set('password_confirmation', 'initial-secret')->call('addMember')->assertHasNoErrors();
        $clerk = User::where('email', 'clerk@example.com')->firstOrFail();
        $this->assertTrue($clerk->hasRole('supplier_staff'));
        $this->assertFalse($clerk->hasRole('supplier_admin'));
        $this->assertTrue($clerk->must_change_password);
        $component->set('name', 'Third')->set('email', 'third@example.com')->set('password', 'initial-secret')->set('password_confirmation', 'initial-secret')
            ->call('addMember')->assertHasErrors('plan');
        $component->call('removeMember', $clerk->id)->assertHasNoErrors();
        $this->assertFalse($clerk->fresh()->is_active);
        $component->call('addMember')->assertHasNoErrors();
    }

    public function test_commission_uses_paid_plan_rate_and_invoices_only_completed_orders(): void
    {
        $this->paid();
        $po = $this->order();
        $this->assertSame(3.0, (float) $po->transaction()->first()->commission_rate);
        $this->assertSame(0, VendorPlatformInvoice::count());
        $po->update(['status' => PurchaseOrderStatus::Completed]);
        app(MarketplaceCommissionService::class)->complete($po);
        $this->assertSame(1, VendorPlatformInvoice::count());
        $this->assertSame(3000.0, (float) VendorPlatformInvoice::first()->total);
    }

    public function test_trial_orders_are_commission_free_even_if_completed_after_expiry(): void
    {
        $this->approve();
        $po = $this->order();
        $this->travelTo($this->subscription->trial_ends_at->copy()->addDay());
        $po->update(['status' => PurchaseOrderStatus::Completed]);
        $this->assertSame(0.0, (float) $po->transaction()->first()->commission_amount);
        $this->assertSame('waived', $po->transaction()->first()->status);
        $this->assertSame(0, VendorPlatformInvoice::count());
    }

    public function test_cancellation_waives_unpaid_commission_and_flags_paid_commission_for_refund(): void
    {
        $this->paid();
        $first = $this->order();
        $first->update(['status' => PurchaseOrderStatus::Cancelled]);
        $this->assertSame('waived', $first->transaction()->first()->status);
        $second = $this->order();
        $second->update(['status' => PurchaseOrderStatus::Completed]);
        $invoice = VendorPlatformInvoice::findOrFail($second->transaction()->first()->platform_invoice_id);
        $invoice->recordPayment((float) $invoice->balance_due, 'other');
        $before = $this->subscription->current_period_end->toDateTimeString();
        app(SelfServiceSubscriptionService::class)->activatePaidInvoice($invoice);
        $this->assertSame($before, $this->subscription->fresh()->current_period_end->toDateTimeString());
        $second->update(['status' => PurchaseOrderStatus::Cancelled]);
        $this->assertSame('refund_due', $second->transaction()->first()->status);
    }

    public function test_supplier_trial_cannot_be_purchased_again(): void
    {
        $this->approve();
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(SelfServiceSubscriptionService::class)->purchaseInvoice($this->subscription, $this->subscription->plan);
    }

    public function test_smaller_plan_requires_reducing_existing_active_usage(): void
    {
        $this->paid();
        $this->listing();
        $this->listing();
        $starter = PricingPlan::where('slug', 'supplier-starter')->firstOrFail();
        $starter->update(['max_active_listings' => 1]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(SelfServiceSubscriptionService::class)->purchaseInvoice($this->subscription, $starter);
    }

    public function test_pending_smaller_plan_also_limits_new_listings_before_payment(): void
    {
        $this->paid();
        $this->listing();
        $starter = PricingPlan::where('slug', 'supplier-starter')->firstOrFail();
        $starter->update(['max_active_listings' => 1]);
        app(SelfServiceSubscriptionService::class)->purchaseInvoice($this->subscription, $starter);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->listing();
    }

    public function test_delivered_and_paid_order_completes_and_invoices_commission(): void
    {
        $this->paid('supplier-pro');
        $po = $this->order();
        $po->update(['status' => PurchaseOrderStatus::Received]);
        $this->assertSame(0, VendorPlatformInvoice::count());
        $po->update(['payment_status' => \App\Domains\Marketplace\Enums\PaymentStatus::Paid]);
        $this->assertSame(PurchaseOrderStatus::Completed, $po->fresh()->status);
        $this->assertSame(2000.0, (float) VendorPlatformInvoice::first()->total);
    }

    public function test_unpaid_commission_invoice_is_cancelled_when_order_is_cancelled(): void
    {
        $this->paid('supplier-starter');
        $po = $this->order();
        $po->update(['status' => PurchaseOrderStatus::Completed]);
        $this->assertSame(5000.0, (float) VendorPlatformInvoice::first()->total);
        $po->update(['status' => PurchaseOrderStatus::Cancelled]);
        $this->assertSame('cancelled', VendorPlatformInvoice::first()->status);
        $this->assertSame('waived', $po->transaction()->first()->status);
    }

    public function test_unapproved_supplier_cannot_buy_a_paid_plan(): void
    {
        $this->actingAs($this->owner);
        Livewire::test(SubscriptionComponent::class)->call('changePlan', PricingPlan::where('slug', 'supplier-starter')->value('id'))->assertHasErrors('plan');
        $this->assertSame(0, VendorPlatformInvoice::count());
    }

    public function test_supplier_registration_rejects_garage_plan_ids(): void
    {
        app(\App\Domains\Platform\Services\ApiIntegrationService::class)->upsertIntegration('onepass', ['base_url' => 'https://onepass.test', 'api_key' => 'key'], true);
        Http::fake(['https://onepass.test/*' => Http::response(['message' => 'OK'])]);
        Livewire::test(\App\Domains\Marketplace\Livewire\Supplier\Registration\Register::class)
            ->set('business_name', 'Other supplier')->set('business_email', 'other-supplier@example.com')->call('nextStep')
            ->set('owner_name', 'Owner')->set('password', 'initial-password')->set('password_confirmation', 'initial-password')
            ->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')->call('nextStep')
            ->set('selectedPlanId', PricingPlan::where('audience', 'garage')->value('id'))->call('nextStep')->assertHasErrors('selectedPlanId');
    }

    public function test_expired_supplier_can_pay_through_checkout_and_restore_listing_visibility(): void
    {
        $this->approve();
        $listing = $this->listing();
        $this->travelTo($this->subscription->trial_ends_at);
        $this->assertFalse(MarketplaceListing::browsable()->whereKey($listing->id)->exists());
        $this->actingAs($this->owner);
        $plan = PricingPlan::where('slug', 'supplier-growth')->firstOrFail();
        Livewire::test(SubscriptionComponent::class)->call('changePlan', $plan->id)->assertHasNoErrors();
        $invoice = $this->subscription->invoices()->firstOrFail();
        app(\App\Domains\Platform\Services\ApiIntegrationService::class)->upsertIntegration('cream_theme', [
            'base_url' => 'https://cream.test', 'api_key' => 'partner-key', 'api_secret' => 'partner-secret', 'environment' => 'test',
        ], true);
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/initiate')) {
                return Http::response(['reference' => $request['reference'], 'txRef' => 'supplier-e2e', 'environment' => 'test',
                    'amount' => $request['amount'], 'currency' => $request['currency'], 'status' => 'pending',
                    'checkoutUrl' => 'https://checkout.flutterwave.com/supplier-test'], 201);
            }
            $attempt = \App\Domains\Platform\Models\SubscriptionPaymentAttempt::where('tx_ref', 'supplier-e2e')->firstOrFail();

            return Http::response(['reference' => $attempt->reference, 'txRef' => $attempt->tx_ref, 'environment' => 'test',
                'amount' => (float) $attempt->amount, 'currency' => $attempt->currency, 'status' => 'successful']);
        });
        $this->post(route('billing.pay', $invoice))->assertRedirect('https://checkout.flutterwave.com/supplier-test');
        $this->assertFalse(app(SupplierPlanAccess::class)->canSell($this->vendor));
        $this->get('/billing/pay/callback?tx_ref=supplier-e2e')->assertRedirect(route('billing.subscription'))->assertSessionHas('success');
        $this->assertTrue(app(SupplierPlanAccess::class)->canSell($this->vendor));
        $this->assertTrue(MarketplaceListing::browsable()->whereKey($listing->id)->exists());
        $this->assertSame($plan->id, $this->subscription->fresh()->pricing_plan_id);
        $this->assertTrue($invoice->fresh()->isPaid());
        $end = $this->subscription->fresh()->current_period_end->toDateTimeString();
        $this->get('/billing/pay/callback?tx_ref=supplier-e2e')->assertSessionHas('success');
        $this->assertSame($end, $this->subscription->fresh()->current_period_end->toDateTimeString());
        $this->assertSame(1, $invoice->payments()->count());
    }

    public function test_early_upgrade_preserves_the_commission_waiver_until_trial_end(): void
    {
        $this->approve();
        $invoice = app(SelfServiceSubscriptionService::class)->purchaseInvoice($this->subscription, PricingPlan::where('slug', 'supplier-growth')->firstOrFail());
        $invoice->recordPayment((float) $invoice->balance_due, 'other');
        app(SelfServiceSubscriptionService::class)->activatePaidInvoice($invoice);
        $early = $this->order();
        $this->assertSame(0.0, (float) $early->transaction()->first()->commission_rate);
        $this->travelTo($this->subscription->fresh()->trial_ends_at);
        $later = $this->order();
        $this->assertSame(3.0, (float) $later->transaction()->first()->commission_rate);
    }

    public function test_existing_order_access_does_not_expose_another_suppliers_order(): void
    {
        $this->approve();
        $po = $this->order();
        $other = Vendor::create(['name' => 'Other supplier', 'email' => 'other-owner@example.com', 'vendor_type' => 'supplier', 'status' => 'active']);
        $otherOwner = User::factory()->create(['vendor_id' => $other->id]);
        $otherOwner->assignRole('supplier_admin');
        $this->actingAs($otherOwner)->get(route('supplier.orders.fulfill', $po))->assertNotFound();
    }
}
