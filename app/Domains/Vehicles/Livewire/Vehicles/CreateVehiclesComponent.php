<?php

namespace App\Domains\Vehicles\Livewire\Vehicles;

use App\Domains\CRM\Models\Customer;
use App\Domains\Vehicles\Models\Vehicle;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\DrivetrainType;
use App\Enums\OwnershipStatus;
use App\Enums\VehicleStatus;
use Livewire\Component;

class CreateVehiclesComponent extends Component
{
    public $customer_id = '';
    public $customerSearch = '';
    public $showCustomerDropdown = false;

    // Basic Vehicle Info
    public $registration_number = '';
    public $make = '';
    public $model = '';
    public $year = '';
    public $color = '';

    // VIN & Identification
    public $vin = '';
    public $chassis_number = '';
    public $engine_number = '';

    // Digital Twin - Engine & Transmission
    public $engine_code = '';
    public $engine_displacement = '';
    public $drivetrain_type = '';
    public $transmission_code = '';
    public $fuel_type = 'gasoline';
    public $transmission_type = 'automatic';
    public $mileage = '';

    // Financial & Lifecycle
    public $in_service_date = '';
    public $acquisition_date = '';
    public $acquisition_cost = '';
    public $ownership_status = 'owned';
    public $lease_end_date = '';
    public $lease_mileage_limit = '';
    public $current_value = '';

    // Status
    public $status = 'active';

    // Notes
    public $notes = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'registration_number' => 'required|string|max:20',
        'vin' => 'nullable|string|size:17',
        'year' => 'nullable|integer|min:1900|max:2035',
        'engine_displacement' => 'nullable|numeric|min:0|max:10',
        'acquisition_cost' => 'nullable|numeric|min:0',
        'current_value' => 'nullable|numeric|min:0',
        'lease_mileage_limit' => 'nullable|integer|min:0',
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
        $vendorId = auth()->user()->vendor_id;
        return Customer::where('vendor_id', $vendorId)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->customerSearch}%")
                  ->orWhere('phone', 'like', "%{$this->customerSearch}%");
})
            ->orderBy('name')->limit(10)->get();
    }

    public function save()
    {
        $this->validate();

        $vehicle = Vehicle::create([
            'vendor_id' => auth()->user()->vendor_id,
            'customer_id' => $this->customer_id,
            'registration_number' => strtoupper($this->registration_number),
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year ?: null,
            'color' => $this->color,
            'vin' => $this->vin ?: null,
            'chassis_number' => $this->chassis_number ?: null,
            'engine_number' => $this->engine_number ?: null,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission_type,
            'mileage' => $this->mileage ?: null,
            'notes' => $this->notes,
            // Digital Twin
            'engine_code' => $this->engine_code ?: null,
            'engine_displacement' => $this->engine_displacement ? (float) $this->engine_displacement : null,
            'drivetrain_type' => $this->drivetrain_type ?: null,
            'transmission_code' => $this->transmission_code ?: null,
            'transmission_type' => $this->transmission_type,
            // Financial & Lifecycle
            'in_service_date' => $this->in_service_date ?: null,
            'acquisition_date' => $this->acquisition_date ?: null,
            'acquisition_cost' => $this->acquisition_cost ? (float) $this->acquisition_cost : null,
            'ownership_status' => $this->ownership_status,
            'lease_end_date' => $this->lease_end_date ?: null,
            'lease_mileage_limit' => $this->lease_mileage_limit ? (int) $this->lease_mileage_limit : null,
            'current_value' => $this->current_value ? (float) $this->current_value : null,
            // Status
            'status' => $this->status,
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
