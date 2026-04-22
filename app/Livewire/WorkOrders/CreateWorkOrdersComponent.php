<?php

namespace App\Livewire\WorkOrders;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ServiceBay;
use App\Models\ServiceTemplate;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Livewire\Component;

class CreateWorkOrdersComponent extends Component
{
    // Customer & Vehicle
    public $customer_id = '';
    public $vehicle_id = '';
    public $customerSearch = '';
    public $showCustomerDropdown = false;
    public $showNewCustomerForm = false;
    public $showNewVehicleForm = false;

    // New Customer fields
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';

    // New Vehicle fields
    public $newVehicleRegNumber = '';
    public $newVehicleMake = '';
    public $newVehicleModel = '';
    public $newVehicleYear = '';
    public $newVehicleColor = '';

    // Work Order details
    public $type = 'service';
    public $priority = 'normal';
    public $service_bay_id = '';
    public $assigned_technician_id = '';
    public $is_combo = false;
    public $mileage_in = '';
    public $customer_notes = '';
    public $estimated_completion = '';

    // Line items
    public $items = [];
    public $selectedTemplate = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'vehicle_id' => 'required|exists:vehicles,id',
        'type' => 'required|in:repair,service,diagnostics,bodywork,electrical,ac,tyres,other',
        'priority' => 'required|in:low,normal,high,urgent',
        'service_bay_id' => 'nullable|exists:service_bays,id',
        'assigned_technician_id' => 'nullable|exists:users,id',
        'mileage_in' => 'nullable|integer|min:0',
        'customer_notes' => 'nullable|string|max:1000',
        'items' => 'array',
        'items.*.item_type' => 'required|in:labor,part',
        'items.*.description' => 'required|string|max:255',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->items = [];
    }

    public function updatedCustomerSearch()
    {
        $this->showCustomerDropdown = strlen($this->customerSearch) >= 2;
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customerSearch = $customer->name . ' - ' . $customer->phone;
            $this->showCustomerDropdown = false;
            $this->vehicle_id = '';
        }
    }

    public function createNewCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'required|string|max:20',
            'newCustomerEmail' => 'nullable|email',
        ]);

        $customer = Customer::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
        ]);

        $this->customer_id = $customer->id;
        $this->customerSearch = $customer->name . ' - ' . $customer->phone;
        $this->showNewCustomerForm = false;
        $this->reset(['newCustomerName', 'newCustomerPhone', 'newCustomerEmail']);
    }

    public function createNewVehicle()
    {
        $this->validate([
            'newVehicleRegNumber' => 'required|string|max:20',
            'newVehicleMake' => 'nullable|string|max:50',
            'newVehicleModel' => 'nullable|string|max:50',
        ]);

        $vehicle = Vehicle::create([
            'customer_id' => $this->customer_id,
            'registration_number' => strtoupper($this->newVehicleRegNumber),
            'make' => $this->newVehicleMake ?: null,
            'model' => $this->newVehicleModel ?: null,
            'year' => $this->newVehicleYear ?: null,
            'color' => $this->newVehicleColor ?: null,
        ]);

        $this->vehicle_id = $vehicle->id;
        $this->showNewVehicleForm = false;
        $this->reset(['newVehicleRegNumber', 'newVehicleMake', 'newVehicleModel', 'newVehicleYear', 'newVehicleColor']);
    }

    public function applyTemplate()
    {
        if (!$this->selectedTemplate) return;

        $template = ServiceTemplate::with('items')->find($this->selectedTemplate);
        if (!$template) return;

        foreach ($template->items as $item) {
            $this->items[] = [
                'item_type' => $item->item_type,
                'description' => $item->description,
                'inventory_item_id' => $item->inventory_item_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        }

        $this->selectedTemplate = '';
    }

    public function addItem($type = 'labor')
    {
        $this->items[] = [
            'item_type' => $type,
            'description' => '',
            'inventory_item_id' => null,
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getCustomersProperty()
    {
        if (strlen($this->customerSearch) < 2) {
            return collect();
        }

        return Customer::where('name', 'like', "%{$this->customerSearch}%")
            ->orWhere('phone', 'like', "%{$this->customerSearch}%")
            ->limit(10)
            ->get();
    }

    public function getVehiclesProperty()
    {
        if (!$this->customer_id) {
            return collect();
        }

        return Vehicle::where('customer_id', $this->customer_id)->get();
    }

    public function getServiceBaysProperty()
    {
        return ServiceBay::where('branch_id', session('current_branch_id'))
            ->where('status', 'available')
            ->get();
    }

    public function getTechniciansProperty()
    {
        return User::role('technician')
            ->where('is_active', true)
            ->whereHas('branches', fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->get();
    }

    public function getTemplatesProperty()
    {
        return ServiceTemplate::where('is_active', true)->get();
    }

    public function getPartsProperty()
    {
        return InventoryItem::where('is_active', true)
            ->whereHas('category', fn($q) => $q->where('type', 'parts'))
            ->where('quantity', '>', 0)
            ->get();
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(function ($item) {
            return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
        });
    }

    public function save()
    {
        $this->validate();

        $workOrder = WorkOrder::create([
            'branch_id' => session('current_branch_id'),
            'customer_id' => $this->customer_id,
            'vehicle_id' => $this->vehicle_id,
            'service_bay_id' => $this->service_bay_id ?: null,
            'assigned_technician_id' => $this->assigned_technician_id ?: null,
            'created_by' => auth()->id(),
            'type' => $this->type,
            'status' => 'open',
            'priority' => $this->priority,
            'is_combo' => $this->is_combo,
            'mileage_in' => $this->mileage_in ?: null,
            'customer_notes' => $this->customer_notes,
            'estimated_completion' => $this->estimated_completion ?: null,
            'checked_in_at' => now(),
        ]);

        foreach ($this->items as $item) {
            $workOrder->items()->create([
                'item_type' => $item['item_type'],
                'description' => $item['description'],
                'inventory_item_id' => $item['inventory_item_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => 0,
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        // Mark bay as occupied if assigned
        if ($workOrder->serviceBay) {
            $workOrder->serviceBay->markAsOccupied();
        }

        session()->flash('success', 'Work order created successfully.');
        return redirect()->route('work-orders.show', $workOrder);
    }

    public function render()
    {
        return view('livewire.work-orders.create-work-orders-component')
            ->layout('components.layouts.app', ['title' => 'New Work Order']);
    }
}
