<?php

namespace App\Livewire\WashOrders;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\WashOrder;
use App\Models\WashPackage;
use Livewire\Component;

class CreateWashOrdersComponent extends Component
{
    public $customer_id = '';
    public $vehicle_id = '';
    public $customerSearch = '';
    public $showCustomerDropdown = false;
    public $showNewCustomerForm = false;
    public $showNewVehicleForm = false;

    public $newCustomerName = '';
    public $newCustomerPhone = '';

    public $newVehicleRegNumber = '';
    public $newVehicleMake = '';
    public $newVehicleModel = '';

    public $wash_type = 'basic';
    public $wash_package_id = '';
    public $priority = 'normal';
    public $customer_notes = '';

    public $items = [];

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'vehicle_id' => 'required|exists:vehicles,id',
        'wash_type' => 'required|in:basic,full,premium,interior,exterior,engine,detailing',
    ];

    public function updatedCustomerSearch()
    {
        $this->showCustomerDropdown = strlen($this->customerSearch) >= 2;
    }

    public function openNewCustomerForm()
    {
        $this->showNewCustomerForm = true;
        $this->showCustomerDropdown = false;
    }

    public function hideNewCustomerForm()
    {
        $this->showNewCustomerForm = false;
    }

    public function openNewVehicleForm()
    {
        $this->showNewVehicleForm = true;
    }

    public function hideNewVehicleForm()
    {
        $this->showNewVehicleForm = false;
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customerSearch = $customer->name . ' - ' . $customer->phone;
            $this->showCustomerDropdown = false;
            $this->vehicle_id = '';
            $this->dispatch('close-customer-dropdown');
        }
    }

    public function createNewCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'required|string|max:20',
        ]);

        $customer = Customer::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
        ]);

        $this->customer_id = $customer->id;
        $this->customerSearch = $customer->name . ' - ' . $customer->phone;
        $this->showNewCustomerForm = false;
        $this->reset(['newCustomerName', 'newCustomerPhone']);
    }

    public function createNewVehicle()
    {
        $this->validate([
            'newVehicleRegNumber' => 'required|string|max:20',
        ]);

        $vehicle = Vehicle::create([
            'customer_id' => $this->customer_id,
            'registration_number' => strtoupper($this->newVehicleRegNumber),
            'make' => $this->newVehicleMake,
            'model' => $this->newVehicleModel,
        ]);

        $this->vehicle_id = $vehicle->id;
        $this->showNewVehicleForm = false;
        $this->reset(['newVehicleRegNumber', 'newVehicleMake', 'newVehicleModel']);
    }

    public function updatedWashPackageId()
    {
        if ($this->wash_package_id) {
            $package = WashPackage::find($this->wash_package_id);
            if ($package) {
                $this->wash_type = $package->wash_type;
                $this->items = [];
                foreach ($package->includes ?? [] as $item) {
                    $this->items[] = [
                        'description' => is_array($item) ? ($item['name'] ?? $item['description'] ?? $item) : $item,
                        'price' => is_array($item) ? ($item['price'] ?? 0) : 0,
                    ];
                }
            }
        }
    }

    public function addItem()
    {
        $this->items[] = ['description' => '', 'price' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
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

    public function getVehiclesProperty()
    {
        if (!$this->customer_id) return collect();
        return Vehicle::where('customer_id', $this->customer_id)->get();
    }

    public function getPackagesProperty()
    {
        return WashPackage::where('is_active', true)->get();
    }

    public function getTotalProperty()
    {
        return collect($this->items)->sum('price');
    }

    public function save()
    {
        if (!session('current_branch_id')) {
            session()->flash('error', 'No active branch selected. Please refresh the page or switch branches.');
            return;
        }

        $this->validate();

        $washOrder = WashOrder::create([
            'branch_id' => session('current_branch_id'),
            'customer_id' => $this->customer_id,
            'vehicle_id' => $this->vehicle_id,
            'wash_package_id' => $this->wash_package_id ?: null,
            'created_by' => auth()->id(),
            'source' => 'walk_in',
            'wash_type' => $this->wash_type,
            'status' => 'queued',
            'priority' => $this->priority,
            'customer_notes' => $this->customer_notes,
            'queue_position' => WashOrder::getNextQueuePosition((int) session('current_branch_id')),
            'queued_at' => now(),
        ]);

        foreach ($this->items as $item) {
            $washOrder->items()->create([
                'description' => $item['description'],
                'quantity' => 1,
                'unit_price' => $item['price'],
                'total' => $item['price'],
            ]);
        }

        session()->flash('success', 'Wash order created and added to queue.');
        return redirect()->route('wash-orders.index');
    }

    public function render()
    {
        return view('livewire.wash-orders.create-wash-orders-component')
            ->layout('components.layouts.app', ['title' => 'New Wash Order']);
    }
}
