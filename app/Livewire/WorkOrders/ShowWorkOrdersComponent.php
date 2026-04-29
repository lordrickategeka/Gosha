<?php

namespace App\Livewire\WorkOrders;

use App\Models\ServiceBay;
use App\Models\User;
use App\Models\WorkOrder;
use Livewire\Component;

class ShowWorkOrdersComponent extends Component
{
    public WorkOrder $workOrder;

    // Quoter pricing
    public $quotingItemPrices = []; // [item_id => ['unit_price' => ..., 'discount' => ...]]
    public $showQuotingPanel = false;
    public $selectedBay = '';
    public $selectedTechnician = '';
    public $technicianNotes = '';

    public function mount(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder->load([
            'vehicle',
            'customer',
            'serviceBay',
            'assignedTechnician',
            'createdBy',
            'items.inventoryItem',
            'items.images',
            'invoice.payments',
            'washOrder',
        ]);

        $this->selectedBay = $workOrder->service_bay_id ?? '';
        $this->selectedTechnician = $workOrder->assigned_technician_id ?? '';

        // Pre-fill quoter pricing fields from existing item prices
        foreach ($workOrder->items as $item) {
            $this->quotingItemPrices[$item->id] = [
                'unit_price' => $item->unit_price,
                'discount'   => $item->discount,
            ];
        }
    }

    public function startWork()
    {
        $this->authorize('change work order status');

        if ($this->workOrder->canStart()) {
            $this->workOrder->start();
            $this->workOrder->refresh();
            session()->flash('success', 'Work started.');
        }
    }

    public function moveToQualityCheck()
    {
        $this->authorize('change work order status');

        $this->workOrder->update([
            'status' => 'quality_check',
            'technician_notes' => $this->technicianNotes,
        ]);
        $this->workOrder->refresh();
        session()->flash('success', 'Moved to quality check.');
    }

    public function markReady()
    {
        $this->authorize('change work order status');

        if ($this->workOrder->canComplete()) {
            $this->workOrder->markReady();
            $this->workOrder->refresh();
            session()->flash('success', 'Work order marked as ready.');
        }
    }

    public function deliver()
    {
        $this->authorize('change work order status');

        if ($this->workOrder->canDeliver()) {
            $this->workOrder->deliver();
            $this->workOrder->refresh();
            session()->flash('success', 'Vehicle delivered to customer.');
        }
    }

    public function assignBayAndTechnician()
    {
        $this->workOrder->update([
            'service_bay_id' => $this->selectedBay ?: null,
            'assigned_technician_id' => $this->selectedTechnician ?: null,
        ]);

        if ($this->selectedBay && $this->workOrder->isInProgress()) {
            ServiceBay::find($this->selectedBay)->markAsOccupied();
        }

        $this->workOrder->refresh();
        $this->showAssignModal = false;
        session()->flash('success', 'Assignment updated.');
    }

    public function cancel()
    {
        $this->authorize('change work order status');

        $this->workOrder->cancel();
        $this->workOrder->refresh();
        session()->flash('success', 'Work order cancelled.');
    }

    public function getAvailableBaysProperty()
    {
        return ServiceBay::where('branch_id', $this->workOrder->branch_id)
            ->where(function ($q) {
                $q->where('status', 'available')
                  ->orWhere('id', $this->workOrder->service_bay_id);
            })
            ->get();
    }

    public function getTechniciansProperty()
    {
        return User::role('technician')
            ->where('is_active', true)
            ->whereHas('branches', fn($q) => $q->where('branch_id', $this->workOrder->branch_id))
            ->get();
    }

    // ─── Quoter actions ──────────────────────────────────────────────────────

    public function saveQuote()
    {
        $this->authorize('price work orders');

        $this->validate([
            'quotingItemPrices.*.unit_price' => 'required|numeric|min:0',
            'quotingItemPrices.*.discount'   => 'nullable|numeric|min:0',
        ]);

        foreach ($this->workOrder->items as $item) {
            if (!isset($this->quotingItemPrices[$item->id])) continue;

            $unitPrice = $this->quotingItemPrices[$item->id]['unit_price'] ?? 0;
            $discount  = $this->quotingItemPrices[$item->id]['discount'] ?? 0;

            $item->update([
                'unit_price' => $unitPrice,
                'discount'   => $discount,
                'total'      => ($item->quantity * $unitPrice) - $discount,
            ]);
        }

        $this->workOrder->update(['status' => 'quoted']);
        $this->workOrder->refresh();
        $this->showQuotingPanel = false;
        session()->flash('success', 'Quote saved. Work order is now quoted.');
    }

    public function render()
    {
        return view('livewire.work-orders.show-work-orders-component')
            ->layout('components.layouts.app', ['title' => 'Work Order ' . $this->workOrder->order_number]);
    }
}
