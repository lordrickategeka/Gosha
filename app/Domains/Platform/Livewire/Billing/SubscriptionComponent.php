<?php

namespace App\Domains\Platform\Livewire\Billing;

use App\Domains\Finance\Services\BillingService;
use App\Domains\Platform\Models\PricingPlan;
use Livewire\Component;

class SubscriptionComponent extends Component
{
    public bool $showPlans = false;

    public function togglePlans()
    {
        $this->showPlans = !$this->showPlans;
    }

    public function toggleAutoRenew()
    {
        $subscription = auth()->user()->vendor?->activeSubscription;
        abort_unless($subscription, 404);

        $subscription->update(['auto_renew' => !$subscription->auto_renew]);

        session()->flash('success', $subscription->auto_renew ? 'Auto-renewal enabled.' : 'Auto-renewal disabled.');
    }

    public function changePlan(int $planId)
    {
        $vendor = auth()->user()->vendor;
        $subscription = $vendor?->activeSubscription;
        $plan = PricingPlan::where('is_active', true)->findOrFail($planId);

        if (!$subscription) {
            app(BillingService::class)->createSubscription($vendor, $plan);
        } else {
            app(BillingService::class)->changePlan($subscription, $plan, true);
        }

        $this->showPlans = false;
        session()->flash('success', "Switched to the {$plan->name} plan.");
    }

    public function render()
    {
        $vendor = auth()->user()->vendor;
        $subscription = $vendor?->activeSubscription?->load('plan');

        $invoices = $subscription
            ? $subscription->invoices()->orderByDesc('issue_date')->limit(20)->get()
            : collect();

        return view('livewire.billing.subscription-component', [
            'vendor' => $vendor,
            'subscription' => $subscription,
            'invoices' => $invoices,
            'plans' => PricingPlan::where('is_active', true)->orderBy('sort_order')->orderBy('base_price')->get(),
        ])->layout('components.layouts.app', ['title' => 'My Subscription']);
    }
}
