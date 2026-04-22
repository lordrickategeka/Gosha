<?php

namespace App\Livewire\Platform\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class Show extends Component
{
    public Vendor $vendor;

    public $editingTrialDays = false;
    public $trialDays = 14;

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

    public function render()
    {
        $this->vendor->load([
            'billingConfig',
            'branches' => fn($q) => $q->withCount('users'),
            'users' => fn($q) => $q->with('roles'),
        ]);

        return view('livewire.platform.vendors.show');
    }
}
