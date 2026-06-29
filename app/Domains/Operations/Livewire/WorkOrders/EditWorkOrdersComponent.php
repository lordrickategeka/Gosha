<?php

namespace App\Domains\Operations\Livewire\WorkOrders;

use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Operations\Models\ServiceBay;
use App\Models\User;
use App\Domains\Operations\Models\WorkOrder;
use App\Domains\Operations\Models\WorkOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EditWorkOrdersComponent extends Component
{
    public WorkOrder $workOrder;

    // Job Details
    public string $type = 'service';
    public string $priority = 'normal';
    public $service_bay_id = null;
    public $assigned_technician_id = null;
    public $mileage_in = null;
    public $mileage_out = null;
    public $estimated_completion = null;
    public bool $is_combo = false;

    // Notes
    public string $customer_notes = '';
    public string $technician_notes = '';
    public array $vehicle_left_items = [];
    public string $left_item_name = '';
    public string $left_item_description = '';
    public $left_item_quantity = 1;
    public string $left_item_reference = '';

    // Items — each item: [id, item_type, description, inventory_item_id, quantity, unit_price, discount]
    public array $items = [];
    public array $deletedItemIds = [];

    // ─── Lifecycle ───────────────────────────────────────────────────────────

    public function mount(WorkOrder $workOrder)
    {
        $this->authorize('edit_work_orders');

        $this->workOrder = $workOrder->load(['items.inventoryItem', 'vehicle', 'customer', 'serviceBay', 'assignedTechnician']);

        $this->type                  = $workOrder->type;
        $this->priority              = $workOrder->priority;
        $this->service_bay_id        = $workOrder->service_bay_id;
        $this->assigned_technician_id = $workOrder->assigned_technician_id;
        $this->mileage_in            = $workOrder->mileage_in;
        $this->mileage_out           = $workOrder->mileage_out;
        $this->estimated_completion  = $workOrder->estimated_completion?->format('Y-m-d\TH:i');
        if (!$this->estimated_completion) {
            $this->estimated_completion = now()->format('Y-m-d\TH:i');
        }
        $this->is_combo              = (bool) $workOrder->is_combo;
        $this->customer_notes        = $workOrder->customer_notes ?? '';
        $this->technician_notes      = $workOrder->technician_notes ?? '';
        $this->vehicle_left_items    = $workOrder->vehicle_items_left ?? [];

        $this->items = $workOrder->items->map(fn($item) => [
            'id'                => $item->id,
            'item_type'         => $item->item_type,
            'description'       => $item->description,
            'inventory_item_id' => $item->inventory_item_id,
            'quantity'          => $item->quantity,
        ])->toArray();
    }

    // ─── Items management ────────────────────────────────────────────────────

    public function addItem(string $type = 'labor'): void
    {
        $this->items[] = [
            'id'                => null,
            'item_type'         => $type,
            'description'       => '',
            'inventory_item_id' => null,
            'quantity'          => 1,
        ];
    }

    public function removeItem(int $index): void
    {
        $item = $this->items[$index] ?? null;

        if ($item && $item['id']) {
            $this->deletedItemIds[] = $item['id'];
        }

        array_splice($this->items, $index, 1);
    }

    public function addVehicleLeftItem(): void
    {
        $itemName = trim($this->left_item_name);
        $reference = trim($this->left_item_reference);

        if ($itemName === '' || (float) $this->left_item_quantity <= 0) {
            return;
        }

        foreach ($this->vehicle_left_items as $existing) {
            if (
                strtolower((string) ($existing['item_name'] ?? '')) === strtolower($itemName)
                && strtolower((string) ($existing['reference'] ?? '')) === strtolower($reference)
            ) {
                return;
            }
        }

        $this->vehicle_left_items[] = [
            'item_name' => $itemName,
            'description' => trim($this->left_item_description),
            'quantity' => (float) $this->left_item_quantity,
            'reference' => $reference,
        ];

        $this->left_item_name = '';
        $this->left_item_description = '';
        $this->left_item_quantity = 1;
        $this->left_item_reference = '';
    }

    public function removeVehicleLeftItem(int $index): void
    {
        if (!isset($this->vehicle_left_items[$index])) {
            return;
        }

        array_splice($this->vehicle_left_items, $index, 1);
    }

    // ─── Computed properties ─────────────────────────────────────────────────

    public function getServiceBaysProperty()
    {
        return ServiceBay::where('branch_id', $this->workOrder->branch_id)
            ->where(function ($q) {
                $q->where('status', 'available')
                  ->orWhere('id', $this->workOrder->service_bay_id);
            })
            ->orderBy('name')
            ->get();
    }

    public function getTechniciansProperty()
    {
        return User::role('technician')
            ->where('is_active', true)
            ->whereHas('branches', fn($q) => $q->where('branch_id', $this->workOrder->branch_id))
            ->get();
    }

    // ─── Save ────────────────────────────────────────────────────────────────

    public function save()
    {
        $this->authorize('edit_work_orders');

        $this->validate([
            'type'                    => 'required|in:repair,service,diagnostics,bodywork,electrical,ac,tyres,other',
            'priority'                => 'required|in:low,normal,high,urgent',
            'service_bay_id'          => 'nullable|integer|exists:service_bays,id',
            'assigned_technician_id'  => 'nullable|integer|exists:users,id',
            'mileage_in'              => 'nullable|integer|min:0',
            'mileage_out'             => 'nullable|integer|min:0',
            'estimated_completion'    => 'nullable|date|after_or_equal:' . now()->subMinute()->format('Y-m-d H:i:s'),
            'customer_notes'          => 'nullable|string|max:1000',
            'technician_notes'        => 'nullable|string|max:1000',
            'vehicle_left_items'                 => 'nullable|array',
            'vehicle_left_items.*.item_name'     => 'required|string|max:255',
            'vehicle_left_items.*.quantity'      => 'required|numeric|min:0.01',
            'vehicle_left_items.*.description'   => 'nullable|string|max:1000',
            'vehicle_left_items.*.reference'     => 'nullable|string|max:100',
            'items'               => 'required|array|min:1',
            'items.*.item_type'   => 'required|in:labor,part',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
        ], [
            'items.required' => 'Please add at least one item.',
            'items.min'      => 'Please add at least one item.',
            'estimated_completion.after_or_equal' => 'Estimated completion must be now or a future date/time.',
        ]);

        try {
            DB::beginTransaction();

            // Track bay change so we can update bay status
            $previousBayId = $this->workOrder->service_bay_id;

            $this->workOrder->update([
                'type'                   => $this->type,
                'priority'               => $this->priority,
                'service_bay_id'         => $this->service_bay_id ?: null,
                'assigned_technician_id' => $this->assigned_technician_id ?: null,
                'mileage_in'             => $this->mileage_in,
                'mileage_out'            => $this->mileage_out,
                'estimated_completion'   => $this->estimated_completion ?: null,
                'is_combo'               => $this->is_combo,
                'customer_notes'         => $this->customer_notes,
                'technician_notes'       => $this->technician_notes,
                'vehicle_items_left'     => !empty($this->vehicle_left_items) ? $this->vehicle_left_items : null,
            ]);

            // Update bay status if bay changed
            if ($previousBayId && $previousBayId !== $this->service_bay_id) {
                ServiceBay::find($previousBayId)?->markAsAvailable();
            }
            if ($this->service_bay_id && $this->workOrder->isInProgress()) {
                ServiceBay::find($this->service_bay_id)?->markAsOccupied();
            }

            // Delete removed items
            if (!empty($this->deletedItemIds)) {
                WorkOrderItem::whereIn('id', $this->deletedItemIds)
                    ->where('work_order_id', $this->workOrder->id)
                    ->delete();
            }

            // Upsert items — prices are set at quotation, not here
            foreach ($this->items as $itemData) {
                if ($itemData['id']) {
                    // Update existing item — preserve existing unit_price/discount/total
                    WorkOrderItem::where('id', $itemData['id'])
                        ->where('work_order_id', $this->workOrder->id)
                        ->update([
                            'item_type'   => $itemData['item_type'],
                            'description' => $itemData['description'],
                            'quantity'    => $itemData['quantity'],
                        ]);
                } else {
                    // New item — price will be set by quoter
                    $this->workOrder->items()->create([
                        'item_type'         => $itemData['item_type'],
                        'description'       => $itemData['description'],
                        'inventory_item_id' => $itemData['inventory_item_id'] ?? null,
                        'quantity'          => $itemData['quantity'],
                        'unit_price'        => 0,
                        'discount'          => 0,
                        'total'             => 0,
                    ]);
                }
            }

            DB::commit();

            session()->flash('success', 'Work order updated successfully.');

            return redirect()->route('work-orders.show', $this->workOrder);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating work order', [
                'id'    => $this->workOrder->id,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Error saving changes: ' . $e->getMessage());
        }
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.work-orders.edit-work-orders-component')
            ->layout('components.layouts.app', ['title' => 'Edit Work Order']);
    }
}

