<?php

namespace App\Livewire\Platform\Vendors;

use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleStatus(Vendor $vendor)
    {
        $vendor->update([
            'status' => $vendor->status === 'suspended' ? 'active' : 'suspended',
        ]);

        session()->flash('success', "Vendor {$vendor->name} has been " . ($vendor->status === 'suspended' ? 'suspended' : 'activated') . '.');
    }

    public function render()
    {
        $query = Vendor::withCount(['branches', 'users'])
            ->with('billingConfig');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $vendors = $query->latest()->paginate(10);

        $stats = [
            'total' => Vendor::count(),
            'active' => Vendor::where('status', 'active')->count(),
            'trial' => Vendor::where('status', 'trial')->count(),
            'suspended' => Vendor::where('status', 'suspended')->count(),
        ];

        return view('livewire.platform.vendors.index', compact('vendors', 'stats'));
    }
}
