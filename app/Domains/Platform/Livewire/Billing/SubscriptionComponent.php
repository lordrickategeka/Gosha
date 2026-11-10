<?php

namespace App\Domains\Platform\Livewire\Billing;

use App\Domains\Platform\Models\PricingPlan;
use Livewire\Component;

class SubscriptionComponent extends Component
{
    public bool $showPlans = false;

    public function togglePlans()
    {
        $this->showPlans = ! $this->showPlans;
    }

    public function toggleAutoRenew()
    {
        abort_unless(app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class)->isOwner(auth()->user()), 403);
        if (app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class)->current(auth()->user()->vendor)?->self_service) {
            session()->flash('error', 'Hosted checkout uses manual renewals. No automatic charge is enabled.');

            return;
        }
        $subscription = auth()->user()->vendor?->activeSubscription;
        abort_unless($subscription, 404);

        $subscription->update(['auto_renew' => ! $subscription->auto_renew]);

        session()->flash('success', $subscription->auto_renew ? 'Auto-renewal enabled.' : 'Auto-renewal disabled.');
    }

    public function changePlan(int $planId)
    {
        abort_unless(app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class)->isOwner(auth()->user()), 403);
        $vendor = auth()->user()->vendor;
        $service = app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class);
        $subscription = $service->current($vendor);
        $plan = PricingPlan::where('is_active', true)->findOrFail($planId);
        if (! $subscription && $vendor->vendor_type === 'supplier') {
            $service->start($vendor, $plan);
        } else {
            abort_unless($subscription, 404);
            $service->purchaseInvoice($subscription, $plan);
        }

        $this->showPlans = false;
        session()->flash('success', ! $subscription && $vendor->vendor_type === 'supplier'
            ? 'Supplier plan selected. Your trial or billing starts after platform approval.'
            : "Your invoice for {$plan->name} is ready. Access changes only after payment is verified.");
    }

    public function render()
    {
        $vendor = auth()->user()->vendor;
        $service = app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class);
        $subscription = $service->current($vendor)?->load('plan');

        $invoices = \App\Domains\Platform\Models\VendorPlatformInvoice::where('vendor_id', $vendor?->id)->orderByDesc('id')->limit(30)->get();

        return view('livewire.billing.subscription-component', [
            'vendor' => $vendor,
            'subscription' => $subscription,
            'invoices' => $invoices,
            'restricted' => $subscription?->self_service && $service->restricted($subscription),
            'isBillingOwner' => $service->isOwner(auth()->user()),
            'isSupplier' => $vendor?->vendor_type === 'supplier',
            'awaitingApproval' => $vendor?->vendor_type === 'supplier' && ! $vendor->is_verified_supplier,
            'plans' => PricingPlan::active()->where('audience', $service->audience($vendor))->when($subscription, fn ($query) => $query->where('is_trial', false))->orderBy('sort_order')->orderBy('base_price')->get()->filter(fn ($plan) => $service->available($plan)),
        ])->layout('components.layouts.app', ['title' => 'My Subscription']);
    }
}
