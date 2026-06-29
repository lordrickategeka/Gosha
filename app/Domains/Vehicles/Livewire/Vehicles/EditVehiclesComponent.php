<?php

namespace App\Domains\Vehicles\Livewire\Vehicles;

use App\Domains\CRM\Models\Customer;
use App\Domains\Vehicles\Models\Vehicle;
use Livewire\Component;

class EditVehiclesComponent extends Component
{
    public Vehicle $vehicle;

    // Owner - Customer Selection
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
        'registration_number' => 'required|string|max:20',
        'vin' => 'nullable|string|size:17',
        'year' => 'nullable|integer|min:1900|max:2035',
        'engine_displacement' => 'nullable|numeric|min:0|max:10',
        'acquisition_cost' => 'nullable|numeric|min:0',
        'current_value' => 'nullable|numeric|min:0',
        'lease_mileage_limit' => 'nullable|integer|min:0',
    ];

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        // Owner - Load existing customer if any
        if ($vehicle->customer_id) {
            $this->customer_id = $vehicle->customer_id;
            $this->customerSearch = $vehicle->customer->name . ' - ' . $vehicle->customer->phone;
        }

        // Basic Vehicle Info
        $this->registration_number = $vehicle->registration_number;
        $this->make = $vehicle->make;
        $this->model = $vehicle->model;
        $this->year = $vehicle->year;
        $this->color = $vehicle->color;

        // VIN & Identification
        $this->vin = $vehicle->vin;
        $this->chassis_number = $vehicle->chassis_number;
        $this->engine_number = $vehicle->engine_number;

        // Digital Twin
        $this->engine_code = $vehicle->engine_code;
        $this->engine_displacement = $vehicle->engine_displacement;
        $this->drivetrain_type = $vehicle->drivetrain_type;
        $this->transmission_code = $vehicle->transmission_code;
        $this->fuel_type = $vehicle->fuel_type ?? 'gasoline';
        $this->transmission_type = $vehicle->transmission_type ?? 'automatic';
        $this->mileage = $vehicle->mileage;

        // Financial & Lifecycle
        $this->in_service_date = $vehicle->in_service_date?->format('Y-m-d');
        $this->acquisition_date = $vehicle->acquisition_date?->format('Y-m-d');
        $this->acquisition_cost = $vehicle->acquisition_cost;
        $this->ownership_status = $vehicle->ownership_status ?? 'owned';
        $this->lease_end_date = $vehicle->lease_end_date?->format('Y-m-d');
        $this->lease_mileage_limit = $vehicle->lease_mileage_limit;
        $this->current_value = $vehicle->current_value;

        // Status
        $this->status = $vehicle->status ?? 'active';

        // Notes
        $this->notes = $vehicle->notes;
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

        $this->vehicle->update([
            'customer_id' => $this->customer_id ?: null,
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

        session()->flash('success', 'Vehicle updated successfully.');
        return redirect()->route('vehicles.show', $this->vehicle);
    }

    public function render()
    {
        return view('livewire.vehicles.edit-vehicles-component')
            ->layout('components.layouts.app', ['title' => 'Edit Vehicle']);
    }
}
