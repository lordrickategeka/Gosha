<?php

namespace App\Domains\Platform\Livewire\Billing;

use App\Domains\Finance\Services\BillingService;
use App\Domains\Platform\Models\PlatformSetting;
use App\Domains\Platform\Models\PricingPlan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class LockedComponent extends Component
{
    public function subscribeToPlan(int $planId)
    {
        $vendor = auth()->user()->vendor;
        abort_unless($vendor, 403);

        $plan = PricingPlan::where('is_active', true)->findOrFail($planId);
        app(BillingService::class)->createSubscription($vendor, $plan);
        $vendor->update(['status' => $plan->has_trial ? 'trial' : 'active']);

        return redirect()->route('billing.subscription');
    }

    public function render()
    {
        $vendor = auth()->user()->vendor;
        $subscription = $vendor?->activeSubscription;
        $outstandingInvoice = $subscription
            ?->invoices()
            ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
            ->latest('due_date')
            ->first();

        return view('livewire.billing.locked-component', [
            'vendor' => $vendor,
            'subscription' => $subscription,
            'outstandingInvoice' => $outstandingInvoice,
            'plans' => PricingPlan::where('is_active', true)->orderBy('sort_order')->orderBy('base_price')->get(),
            'exportEnabled' => (bool) PlatformSetting::get(PlatformSetting::BILLING_LOCKDOWN_EXPORT_ENABLED, true),
            'supportEnabled' => (bool) PlatformSetting::get(PlatformSetting::BILLING_LOCKDOWN_SUPPORT_ENABLED, true),
        ]);
    }
}
