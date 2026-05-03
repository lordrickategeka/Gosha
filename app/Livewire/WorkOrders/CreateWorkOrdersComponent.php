<?php

namespace App\Livewire\WorkOrders;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ServiceBay;
use App\Models\ServiceTemplate;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateWorkOrdersComponent extends Component
{
    use WithFileUploads;

    // Wizard step
    public int $currentStep = 1;
    public int $totalSteps = 4;

    // Customer & Vehicle - bound to CustomerVehicleSelector via wire:model
    public $customer_id = null;
    public $vehicle_id = null;

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

    // Per-item image uploads (keyed by item index)
    public $itemImages = [];

    public function mount()
    {
        $this->items = [];
    }

    // ─── Child component event listeners ────────────────────────────────────

    #[On('customerSelected')]
    public function handleCustomerSelected($customerId): void
    {
        $this->customer_id = $customerId;
        $this->vehicle_id = null;
    }

    #[On('vehicleSelected')]
    public function handleVehicleSelected($vehicleId): void
    {
        $this->vehicle_id = $vehicleId;
    }

    // ─── Wizard navigation ───────────────────────────────────────────────────

    public function nextStep()
    {
        $this->validateCurrentStep();
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate([
                'customer_id' => 'required|exists:customers,id',
                'vehicle_id'  => 'required|exists:vehicles,id',
            ]),
            2 => $this->validate([
                'type'     => 'required|in:repair,service,diagnostics,bodywork,electrical,ac,tyres,other',
                'priority' => 'required|in:low,normal,high,urgent',
                'service_bay_id' => 'nullable|exists:service_bays,id',
                'assigned_technician_id' => 'nullable|exists:users,id',
                'mileage_in' => 'nullable|integer|min:0',
            ]),
            3 => $this->validate([
                'items' => 'array|min:1',
                'items.*.item_type'   => 'required|in:labor,part',
                'items.*.description' => 'required|string|max:255',
                'items.*.quantity'    => 'required|numeric|min:0.01',
            ]),
            default => null,
        };
    }

    // ─── Items helpers ───────────────────────────────────────────────────────

    public function applyTemplate()
    {
        if (!$this->selectedTemplate) return;

        $template = ServiceTemplate::with('items')->find($this->selectedTemplate);
        if (!$template) return;

        foreach ($template->items as $item) {
            $this->items[] = [
                'item_type'         => $item->item_type,
                'description'       => $item->description,
                'inventory_item_id' => $item->inventory_item_id,
                'quantity'          => $item->quantity,
                'unit_price'        => $item->unit_price,
            ];
        }

        $this->selectedTemplate = '';
    }

    public function addItem($type = 'labor')
    {
        $this->items[] = [
            'item_type'         => $type,
            'description'       => '',
            'inventory_item_id' => null,
            'quantity'          => 1,
            'unit_price'        => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        unset($this->itemImages[$index]);
        $this->items = array_values($this->items);
        $this->itemImages = array_values($this->itemImages);
    }

    // ─── Computed properties ─────────────────────────────────────────────────

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
        return collect($this->items)->sum(fn($item) =>
            ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)
        );
    }

    public function isJobcarder(): bool
    {
        return auth()->user()->hasRole('jobcarder');
    }

    // ─── Submit ──────────────────────────────────────────────────────────────

    public function save()
    {
        // Add debug logging
        Log::info('Save method called', [
            'customer_id' => $this->customer_id,
            'vehicle_id' => $this->vehicle_id,
            'items_count' => count($this->items),
            'branch_id' => session('current_branch_id'),
        ]);

        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id'  => 'required|exists:vehicles,id',
            'items'       => 'array|min:1',
        ]);

        // Check closure time
        $vendor = auth()->user()->vendor;
        $settings = $vendor?->settings ?? [];
        if (!empty($settings['closure_enabled']) && !empty($settings['closure_time'])) {
            $now = now()->format('H:i');
            if ($now >= $settings['closure_time']) {
                $this->addError('closure', 'New orders cannot be created after ' . $settings['closure_time'] . '. The system is closed for the day.');
                return;
            }
        }

        try {
            $workOrder = WorkOrder::create([
                'branch_id'               => session('current_branch_id'),
                'customer_id'             => $this->customer_id,
                'vehicle_id'              => $this->vehicle_id,
                'service_bay_id'          => $this->service_bay_id ?: null,
                'assigned_technician_id'  => $this->assigned_technician_id ?: null,
                'created_by'              => auth()->id(),
                'type'                    => $this->type,
                'status'                  => 'open',
                'priority'                => $this->priority,
                'is_combo'                => $this->is_combo,
                'mileage_in'              => $this->mileage_in ?: null,
                'customer_notes'          => $this->customer_notes,
                'estimated_completion'    => $this->estimated_completion ?: null,
                'checked_in_at'           => now(),
            ]);

            Log::info('Work order created', ['id' => $workOrder->id]);

            foreach ($this->items as $index => $item) {
                $workOrderItem = $workOrder->items()->create([
                    'item_type'         => $item['item_type'],
                    'description'       => $item['description'],
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $this->isJobcarder() ? 0 : ($item['unit_price'] ?? 0),
                    'discount'          => 0,
                    'total'             => $this->isJobcarder() ? 0 : ($item['quantity'] * ($item['unit_price'] ?? 0)),
                ]);

                // Attach images for this item
                if (!empty($this->itemImages[$index])) {
                    $images = is_array($this->itemImages[$index])
                        ? $this->itemImages[$index]
                        : [$this->itemImages[$index]];

                    foreach ($images as $img) {
                        $path = $img->store('work-order-items', 'public');
                        $workOrderItem->images()->create(['path' => $path]);
                    }
                }
            }

            if ($workOrder->serviceBay) {
                $workOrder->serviceBay->markAsOccupied();
            }

            session()->flash('success', 'Work order created successfully.');
            return redirect()->route('work-orders.show', $workOrder);
        } catch (\Exception $e) {
            Log::error('Error creating work order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->addError('save', 'Error creating work order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.work-orders.create-work-orders-component')
            ->layout('components.layouts.app', ['title' => 'New Work Order']);
    }
}
