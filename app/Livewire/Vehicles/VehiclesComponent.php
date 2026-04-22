<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

class VehiclesComponent extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $vehicles = Vehicle::with(['customer'])
            ->whereHas('customer', fn($q) => $q->where('vendor_id', auth()->user()->vendor_id))
            ->when($this->search, fn($q) => $q->where('registration_number', 'like', "%{$this->search}%")
                ->orWhere('make', 'like', "%{$this->search}%")
                ->orWhere('model', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(15);

        return view('livewire.vehicles.vehicles-component', compact('vehicles'))
            ->layout('components.layouts.app', ['title' => 'Vehicles']);
    }
}
