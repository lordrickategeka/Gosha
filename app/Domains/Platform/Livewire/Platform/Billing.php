<?php

namespace App\Domains\Platform\Livewire\Platform;

use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorSubscription;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use Livewire\Component;

class Billing extends Component
{
    public function getStatsProperty()
    {
        $activeSubs = VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)
            ->with('plan')
            ->get();

        $mrr = $activeSubs->sum(fn ($s) => $s->getEffectivePrice());

        return [
            'mrr'               => $mrr,
            'active_subscriptions' => VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)->count(),
            'active_trials'     => VendorSubscription::where('status', VendorSubscription::STATUS_TRIAL)
                ->where('trial_ends_at', '>', now())->count(),
            'past_due'          => VendorSubscription::where('status', VendorSubscription::STATUS_PAST_DUE)->count(),
            'total_vendors'     => Vendor::where('status', '!=', 'suspended')->count(),
            'outstanding_balance' => VendorPlatformInvoice::where('status', 'pending')
                ->sum('balance_due'),
        ];
    }

    public function getVendorsByPlanProperty()
    {
        return VendorSubscription::active()
            ->join('pricing_plans', 'vendor_subscriptions.pricing_plan_id', '=', 'pricing_plans.id')
            ->selectRaw('pricing_plans.name as plan_name, COUNT(*) as count')
            ->groupBy('pricing_plans.name')
            ->pluck('count', 'plan_name');
    }

    public function getExpiringTrialsProperty()
    {
        return VendorSubscription::where('status', VendorSubscription::STATUS_TRIAL)
            ->where('trial_ends_at', '<=', now()->addDays(7))
            ->where('trial_ends_at', '>', now()->subDay())
            ->with(['vendor', 'plan'])
            ->orderBy('trial_ends_at')
            ->get();
    }

    public function getRecentInvoicesProperty()
    {
        return VendorPlatformInvoice::with('vendor')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.platform.billing')
            ->layout('components.layouts.app', ['title' => 'Platform Billing']);
    }
}
