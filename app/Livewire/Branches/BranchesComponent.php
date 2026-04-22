<?php

namespace App\Livewire\Branches;

use App\Models\Branch;
use Livewire\Component;

class BranchesComponent extends Component
{
    public $showCreateModal = false;
    public $name = '';
    public $address = '';
    public $phone = '';

    public function switchBranch($branchId)
    {
        $branch = Branch::find($branchId);
        if ($branch && $branch->vendor_id === auth()->user()->vendor_id) {
            session(['current_branch_id' => $branchId]);
            session()->flash('success', "Switched to {$branch->name}");
        }
        return redirect()->route('dashboard');
    }

    public function createBranch()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $vendorId = $this->resolveVendorId();
        if (!$vendorId) {
            session()->flash('error', 'Unable to determine vendor. Please switch to a vendor account.');
            return;
        }

        Branch::create([
            'vendor_id' => $vendorId,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_active' => true,
        ]);

        $this->reset(['name', 'address', 'phone', 'showCreateModal']);
        session()->flash('success', 'Branch created successfully.');
    }

    public function getBranchesProperty()
    {
        $vendorId = $this->resolveVendorId();
        if (!$vendorId) {
            return collect();
        }

        return Branch::where('vendor_id', $vendorId)
            ->withCount(['workOrders', 'washOrders', 'users'])
            ->get();
    }

    protected function resolveVendorId(): ?int
    {
        if (auth()->user()->vendor_id) {
            return auth()->user()->vendor_id;
        }

        if (session('current_branch_id')) {
            return Branch::find(session('current_branch_id'))?->vendor_id;
        }

        return null;
    }

    public function render()
    {
        return view('livewire.branches.branches-component')
            ->layout('components.layouts.app', ['title' => 'Branches']);
    }
}
