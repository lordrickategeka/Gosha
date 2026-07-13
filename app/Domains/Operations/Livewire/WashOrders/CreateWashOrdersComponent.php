<?php

namespace App\Domains\Operations\Livewire\WashOrders;

use App\Domains\Operations\Models\WashOrder;
use App\Domains\ServiceConfig\Models\WashPackage;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateWashOrdersComponent extends Component
{
    public $customer_id = '';
    public $vehicle_id = '';

    public $wash_type = 'basic';
    public $wash_package_id = '';
    public $priority = 'normal';
    public $source = 'walk_in';
    public $customer_notes = '';

    public $items = [];

    #[On('customerSelected')]
    public function onCustomerSelected($customerId): void
    {
        $this->customer_id = $customerId ?? '';
        $this->vehicle_id = '';
    }

    #[On('vehicleSelected')]
    public function onVehicleSelected($vehicleId): void
    {
        $this->vehicle_id = $vehicleId ?? '';
    }

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'vehicle_id' => 'required|exists:vehicles,id',
        'wash_type' => 'required|in:basic,standard,premium,interior,exterior,engine,full_detail,custom',
    ];

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
            'source' => $this->source,
            'wash_type' => $this->wash_type,
            'status' => 'queued',
            'priority' => $this->priority,
            'notes' => $this->customer_notes,
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
