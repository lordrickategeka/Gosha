<?php

namespace App\Domains\Platform\Livewire\Platform\Vendors;

use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Finance\Services\BillingService;
use Livewire\Component;

class Show extends Component
{
    public Vendor $vendor;

    public $editingTrialDays = false;
    public $trialDays = 14;

    // Subscription assignment modal
    public $showAssignPlanModal = false;
    public $selectedPlanId = null;
    public $customPrice = null;
    public $discountPercent = 0;
    public $discountReason = '';
    public $waiveSetupFee = false;

    public function mount(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    public function toggleStatus()
    {
        $newStatus = $this->vendor->status === 'suspended' ? 'active' : 'suspended';
        $this->vendor->update(['status' => $newStatus]);
        $this->vendor->refresh();

        session()->flash('success', "Vendor has been {$newStatus}.");
    }

    public function activateFromTrial()
    {
        $this->vendor->update([
            'status' => 'active',
            'trial_ends_at' => null,
        ]);
        $this->vendor->refresh();

        session()->flash('success', 'Vendor has been activated.');
    }

    public function showTrialEdit()
    {
        $this->trialDays = $this->vendor->trial_ends_at
            ? max(0, (int) now()->diffInDays($this->vendor->trial_ends_at, false))
            : 14;
        $this->editingTrialDays = true;
    }

    public function updateTrialPeriod()
    {
        $this->validate(['trialDays' => 'required|integer|min:0|max:365']);

        $this->vendor->update([
            'trial_ends_at' => now()->addDays($this->trialDays),
            'status' => 'trial',
        ]);
        $this->vendor->refresh();
        $this->editingTrialDays = false;

        session()->flash('success', 'Trial period updated.');
    }

    public function openAssignPlan()
    {
        $sub = $this->vendor->activeSubscription;
        $this->selectedPlanId = $sub?->pricing_plan_id;
        $this->customPrice = $sub?->custom_base_price;
        $this->discountPercent = $sub?->discount_percent ?? 0;
        $this->discountReason = $sub?->discount_reason ?? '';
        $this->waiveSetupFee = false;
        $this->showAssignPlanModal = true;
    }

    public function closeAssignPlan()
    {
        $this->showAssignPlanModal = false;
    }

    public function assignPlan()
    {
        $this->validate([
            'selectedPlanId' => 'required|exists:pricing_plans,id',
            'discountPercent' => 'nullable|numeric|min:0|max:100',
            'customPrice' => 'nullable|numeric|min:0',
        ]);

        $plan = PricingPlan::findOrFail($this->selectedPlanId);

        app(BillingService::class)->createSubscription($this->vendor, $plan, [
            'custom_price'    => $this->customPrice ?: null,
            'discount_percent' => $this->discountPercent ?: 0,
            'discount_reason' => $this->discountReason ?: null,
            'waive_setup_fee' => $this->waiveSetupFee,
        ]);

        $this->vendor->refresh();
        $this->showAssignPlanModal = false;

        session()->flash('success', "Plan \"{$plan->name}\" assigned to {$this->vendor->name}.");
    }

    public function cancelSubscription()
    {
        $sub = $this->vendor->activeSubscription;
        if ($sub) {
            $sub->cancel('Cancelled by platform admin', true);
            $this->vendor->refresh();
            session()->flash('success', 'Subscription cancelled.');
        }
    }

    public function getPlansProperty()
    {
        return PricingPlan::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function render()
    {
        $this->vendor->load([
            'billingConfig',
            'activeSubscription.plan',
            'branches' => fn($q) => $q->withCount('users'),
            'users' => fn($q) => $q->with('roles'),
        ]);

        return view('livewire.platform.vendors.show');
    }
}
