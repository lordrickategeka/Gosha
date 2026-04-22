<?php

namespace App\Livewire\Vehicles;

use App\Models\Customer;
use App\Models\Vehicle;
use Livewire\Component;

class CreateVehiclesComponent extends Component
{
    public $customer_id = '';
    public $customerSearch = '';
    public $showCustomerDropdown = false;
    public $registration_number = '';
    public $make = '';
    public $model = '';
    public $year = '';
    public $color = '';
    public $vin = '';
    public $engine_number = '';
    public $fuel_type = 'petrol';
    public $transmission = 'automatic';
    public $notes = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'registration_number' => 'required|string|max:20',
        'make' => 'nullable|string|max:50',
        'model' => 'nullable|string|max:50',
        'year' => 'nullable|integer|min:1900|max:2030',
        'color' => 'nullable|string|max:30',
        'vin' => 'nullable|string|max:50',
    ];

    public function mount()
    {
        if (request('customer')) {
            $customer = Customer::find(request('customer'));
            if ($customer) {
                $this->customer_id = $customer->id;
                $this->customerSearch = $customer->name . ' - ' . $customer->phone;
            }
        }
    }

    public function updatedCustomerSearch()
    {
        $this->showCustomerDropdown = strlen($this->customerSearch) >= 2;
    }

    public function selectCustomer($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customerSearch = $customer->name . ' - ' . $customer->phone;
            $this->showCustomerDropdown = false;
        }
    }

    public function getCustomersProperty()
    {
        if (strlen($this->customerSearch) < 2) return collect();
        return Customer::where('name', 'like', "%{$this->customerSearch}%")
            ->orWhere('phone', 'like', "%{$this->customerSearch}%")
            ->limit(10)->get();
    }

    public function save()
    {
        $this->validate();

        $vehicle = Vehicle::create([
            'customer_id' => $this->customer_id,
            'registration_number' => strtoupper($this->registration_number),
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year ?: null,
            'color' => $this->color,
            'vin' => $this->vin,
            'engine_number' => $this->engine_number,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Vehicle added successfully.');
        return redirect()->route('vehicles.show', $vehicle);
    }

    public function render()
    {
        return view('livewire.vehicles.create-vehicles-component')
            ->layout('components.layouts.app', ['title' => 'Add Vehicle']);
    }
}
