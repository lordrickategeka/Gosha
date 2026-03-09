<?php

namespace App\Livewire\Vehicles;

use Livewire\Component;
use App\Models\Vehicle;
use App\Models\Customer;
use Livewire\WithFileUploads;

class AllVehicles extends Component
{
    use WithFileUploads;

    public $vehicles;
    public $search = '';
    public $importFile;
    public $filterCustomer = '';
    public $selectedVehicle;

    public function mount()
    {
        $this->vehicles = Vehicle::all();
    }

    public function updatedSearch()
    {
        $this->vehicles = Vehicle::query()
            ->where('vehicle_name', 'like', "%{$this->search}%")
            ->orWhere('number_plate', 'like', "%{$this->search}%")
            ->get();
    }

    public function updatedFilterCustomer()
    {
        $this->vehicles = Vehicle::query()
            ->when($this->filterCustomer, function ($query) {
                $query->where('customer_id', $this->filterCustomer);
            })
            ->get();
    }

    public function export()
    {
        // Export logic here
        session()->flash('success', 'Vehicles exported successfully.');
    }

    public function import()
    {
        // Import logic here
        session()->flash('success', 'Vehicles imported successfully.');
    }

    public function showDetails($vehicleId)
    {
        $this->selectedVehicle = Vehicle::with('customer')->find($vehicleId);

        if (!$this->selectedVehicle) {
            session()->flash('error', 'Vehicle not found.');
        }
    }

    public function render()
    {
        return view('livewire.vehicles.all-vehicles', [
            'vehicles' => $this->vehicles,
            'customers' => Customer::all(),
        ]);
    }
}
