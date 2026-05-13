<?php

namespace App\Livewire\WorkOrders;

use App\Models\QualityCheckTemplate;
use App\Models\Quotation;
use App\Models\ServiceBay;
use App\Models\User;
use App\Models\WorkOrder;
use Livewire\Component;

class ShowWorkOrdersComponent extends Component
{
    public WorkOrder $workOrder;

    public $showAssignModal = false;
    public $selectedBay = '';
    public $selectedTechnician = '';
    public $technicianNotes = '';
    public $activeTab = 'job-items';

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
            'qualityCheck.inspector',
            'qualityCheck.items',
        ]);

        $this->ensureQualityChecklistItems();

        $this->selectedBay = $workOrder->service_bay_id ?? '';
        $this->selectedTechnician = $workOrder->assigned_technician_id ?? '';
    }

    protected function ensureQualityChecklistItems(): void
    {
        // Create QualityCheck record if it doesn't exist
        if (!$this->workOrder->qualityCheck && $this->workOrder->status === 'quality_check') {
            $qualityCheck = $this->workOrder->qualityCheck()->create([
                'vehicle_id' => $this->workOrder->vehicle_id,
                'customer_id' => $this->workOrder->customer_id,
                'vendor_id' => $this->workOrder->vendor_id,
                'branch_id' => $this->workOrder->branch_id,
                'inspector_user_id' => auth()->id(),
                'inspection_date' => now()->date(),
                'status' => 'pending',
            ]);
        } else {
            $qualityCheck = $this->workOrder->qualityCheck;
        }

        if (!$qualityCheck || $qualityCheck->items->isNotEmpty()) {
            return;
        }

        $templates = QualityCheckTemplate::getForVendor($this->workOrder->vendor_id);
        $requiresRoadTest = $this->workOrder->items()
            ->where('description', 'like', '%road test%')
            ->exists();

        foreach ($templates as $section => $items) {
            if ($section === 'road_test' && !$requiresRoadTest) {
                continue;
            }

            foreach ($items as $template) {
                $qualityCheck->items()->create([
                    'section' => $template->section,
                    'item_name' => $template->item_name,
                    'status' => null,
                    'remarks' => null,
                ]);
            }
        }

        $this->workOrder->load('qualityCheck.items');
    }

    public function startWork()
    {
        $this->authorize('change_work_order_status');

        if ($this->workOrder->canStart()) {
            $this->workOrder->start();
            $this->workOrder->refresh();
            session()->flash('success', 'Work started.');
        }
    }

    public function moveToQualityCheck()
    {
        $this->authorize('change_work_order_status');

        $this->workOrder->update([
            'status' => 'quality_check',
            'technician_notes' => $this->technicianNotes,
        ]);

        $this->workOrder = $this->workOrder->fresh([
            'vehicle',
            'customer',
            'serviceBay',
            'assignedTechnician',
            'createdBy',
            'items.inventoryItem',
            'items.images',
            'invoice.payments',
            'washOrder',
            'qualityCheck.inspector',
            'qualityCheck.items',
        ]);
        $this->ensureQualityChecklistItems();

        session()->flash('success', 'Moved to quality check.');
    }

    public function markReady()
    {
        $this->authorize('change_work_order_status');

        if ($this->workOrder->canComplete()) {
            $this->workOrder->markReady();
            $this->workOrder->refresh();
            session()->flash('success', 'Work order marked as ready.');
        }
    }

    public function deliver()
    {
        $this->authorize('change_work_order_status');

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
        $this->authorize('change_work_order_status');

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

    public function getLatestQuotationProperty(): ?Quotation
    {
        return $this->workOrder->latestQuotation;
    }

    public function getGroupedQualityCheckItemsProperty()
    {
        if (!$this->workOrder->qualityCheck || $this->workOrder->qualityCheck->items->isEmpty()) {
            return [];
        }

        $sections = [
            'exterior' => 'A. Exterior',
            'interior' => 'B. Interior',
            'engine_compartment' => 'C. Engine Compartment',
            'underbody_suspension' => 'D. Underbody & Suspension',
            'road_test' => 'E. Road Test',
        ];

        $grouped = [];
        foreach ($sections as $key => $label) {
            $items = $this->workOrder->qualityCheck->items
                ->where('section', $key)
                ->values()
                ->toArray();
            if (!empty($items)) {
                $grouped[$key] = $label;
            }
        }

        return $grouped;
    }

    public function render()
    {
        return view('livewire.work-orders.show-work-orders-component')
            ->layout('components.layouts.app', ['title' => 'Work Order ' . $this->workOrder->order_number]);
    }
}
