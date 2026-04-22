<?php

namespace App\Livewire\Platform;

use App\Models\Vendor;
use Livewire\Component;

class Billing extends Component
{
    public function getStatsProperty()
    {
        return [
            'mrr' => Vendor::where('is_active', true)
                ->where('plan', '!=', 'trial')
                ->count() * 150000, // Simplified MRR calc
            'trial_conversions' => Vendor::where('plan', '!=', 'trial')
                ->whereNotNull('trial_ends_at')
                ->count(),
            'active_trials' => Vendor::where('plan', 'trial')
                ->where('is_active', true)
                ->count(),
            'churn_risk' => Vendor::where('plan', 'trial')
                ->where('trial_ends_at', '<', now()->addDays(3))
                ->count(),
        ];
    }

    public function getVendorsByPlanProperty()
    {
        return Vendor::selectRaw('plan, COUNT(*) as count')
            ->where('is_active', true)
            ->groupBy('plan')
            ->pluck('count', 'plan');
    }

    public function getExpiringTrialsProperty()
    {
        return Vendor::where('plan', 'trial')
            ->where('trial_ends_at', '<=', now()->addDays(7))
            ->where('is_active', true)
            ->orderBy('trial_ends_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.platform.billing')
            ->layout('components.layouts.app', ['title' => 'Platform Billing']);
    }
}
