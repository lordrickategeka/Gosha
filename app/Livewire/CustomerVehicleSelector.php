<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Vehicle;
use Livewire\Component;

class CustomerVehicleSelector extends Component
{
    // Public properties that parent components can bind to
    public $customerId = null;
    public $vehicleId = null;

    // Internal state
    public $customerSearch = '';
    public $showCustomerDropdown = false;
    public $showNewCustomerForm = false;
    public $showNewVehicleForm = false;

    // New customer fields
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';

    // New vehicle fields
    public $newVehicleRegNumber = '';
    public $newVehicleMake = '';
    public $newVehicleModel = '';
    public $newVehicleYear = '';
    public $newVehicleColor = '';
    public $newVehicleChassisNumber = '';

    // Configuration
    public $showVehicleSelector = true;
    public $allowNewCustomer = true;
    public $allowNewVehicle = true;
    public $searchPlaceholder = 'Search by name or phone...';
    public $minSearchLength = 2;

    protected $listeners = ['resetCustomerSelector' => 'resetState'];

    // ─── Customer Search ─────────────────────────────────────────────────────

    public function updatedCustomerSearch()
    {
        $this->showCustomerDropdown = strlen($this->customerSearch) >= $this->minSearchLength;

        // If search is cleared, also clear selection
        if (empty($this->customerSearch)) {
            $this->customerId = null;
            $this->vehicleId = null;
        }
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customerId = $customer->id;
            $this->customerSearch = $customer->name . ' - ' . $customer->phone;
            $this->showCustomerDropdown = false;
            $this->vehicleId = null; // Reset vehicle selection

            // Notify parent component
            $this->dispatch('customerSelected', customerId: $customer->id);
        }
    }

    // ─── New Customer ────────────────────────────────────────────────────────

    public function toggleNewCustomerForm()
    {
        $this->showNewCustomerForm = !$this->showNewCustomerForm;
        $this->showCustomerDropdown = false;

        if (!$this->showNewCustomerForm) {
            $this->resetNewCustomerFields();
        }
    }

    public function createNewCustomer()
    {
        $this->validate([
            'newCustomerName'  => 'required|string|max:255',
            'newCustomerPhone' => 'required|string|max:20',
            'newCustomerEmail' => 'nullable|email|max:255',
        ]);

        $customer = Customer::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name'      => $this->newCustomerName,
            'phone'     => $this->newCustomerPhone,
            'email'     => $this->newCustomerEmail,
        ]);

        $this->customerId = $customer->id;
        $this->customerSearch = $customer->name . ' - ' . $customer->phone;
        $this->showNewCustomerForm = false;
        $this->resetNewCustomerFields();

        // Notify parent component
        $this->dispatch('customerSelected', customerId: $customer->id);
        $this->dispatch('customerCreated', customerId: $customer->id);

        session()->flash('customer-selector-success', 'Customer created successfully.');
    }

    protected function resetNewCustomerFields()
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
    }

    // ─── New Vehicle ─────────────────────────────────────────────────────────

    public function toggleNewVehicleForm()
    {
        $this->showNewVehicleForm = !$this->showNewVehicleForm;

        if (!$this->showNewVehicleForm) {
            $this->resetNewVehicleFields();
        }
    }

    public function createNewVehicle()
    {
        $this->validate([
            'newVehicleRegNumber' => 'required|string|max:20|unique:vehicles,registration_number',
            'newVehicleMake'      => 'nullable|string|max:50',
            'newVehicleModel'     => 'nullable|string|max:50',
            'newVehicleYear'      => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehicle = Vehicle::create([
            'customer_id'         => $this->customerId,
            'registration_number' => strtoupper($this->newVehicleRegNumber),
            'make'                => $this->newVehicleMake ?: null,
            'model'               => $this->newVehicleModel ?: null,
            'year'                => $this->newVehicleYear ?: null,
            'color'               => $this->newVehicleColor ?: null,
            'chassis_number'      => $this->newVehicleChassisNumber ?: null,
        ]);

        $this->vehicleId = $vehicle->id;
        $this->showNewVehicleForm = false;
        $this->resetNewVehicleFields();

        // Notify parent component
        $this->dispatch('vehicleSelected', vehicleId: $vehicle->id);
        $this->dispatch('vehicleCreated', vehicleId: $vehicle->id);

        session()->flash('customer-selector-success', 'Vehicle added successfully.');
    }

    protected function resetNewVehicleFields()
    {
        $this->newVehicleRegNumber = '';
        $this->newVehicleMake = '';
        $this->newVehicleModel = '';
        $this->newVehicleYear = '';
        $this->newVehicleColor = '';
        $this->newVehicleChassisNumber = '';
    }

    // ─── Computed Properties ─────────────────────────────────────────────────

    public function getCustomersProperty()
    {
        $vendorId = auth()->user()->vendor_id;

        if (strlen($this->customerSearch) < $this->minSearchLength) {
            return collect();
        }

        return Customer::where('vendor_id', $vendorId)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->customerSearch}%")
                  ->orWhere('phone', 'like', "%{$this->customerSearch}%")
                  ->orWhere('email', 'like', "%{$this->customerSearch}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function getVehiclesProperty()
    {
        if (!$this->customerId) {
            return collect();
        }

        return Vehicle::where('customer_id', $this->customerId)
            ->orderBy('registration_number')
            ->get();
    }

    public function getSelectedCustomerProperty()
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
    }

    public function getSelectedVehicleProperty()
    {
        return $this->vehicleId ? Vehicle::find($this->vehicleId) : null;
    }

    // ─── Utilities ───────────────────────────────────────────────────────────

    public function resetState()
    {
        $this->customerId = null;
        $this->vehicleId = null;
        $this->customerSearch = '';
        $this->showCustomerDropdown = false;
        $this->showNewCustomerForm = false;
        $this->showNewVehicleForm = false;
        $this->resetNewCustomerFields();
        $this->resetNewVehicleFields();
    }

    public function render()
    {
        return view('livewire.customer-vehicle-selector');
    }
}
