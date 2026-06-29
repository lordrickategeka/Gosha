<?php

namespace App\Domains\Vehicles\Livewire\Vehicles;

use App\Domains\Vehicles\Models\Vehicle;
use Livewire\Component;

class ShowVehiclesComponent extends Component
{
    public Vehicle $vehicle;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle->load([
            'customer',
            'workOrders' => fn($q) => $q->latest()->take(10),
            'washOrders' => fn($q) => $q->latest()->take(10),
            'serviceHistories' => fn($q) => $q->latest()->take(10),
        ]);
    }

    public function render()
    {
        return view('livewire.vehicles.show-vehicles-component')
            ->layout('components.layouts.app', ['title' => $this->vehicle->registration_number]);
    }
}
