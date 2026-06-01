<?php

namespace App\Livewire\WorkOrders;

use App\Models\DebitNote;
use App\Models\QualityCheckTemplate;
use App\Models\Quotation;
use App\Models\ServiceBay;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShowWorkOrdersComponent extends Component
{
    public WorkOrder $workOrder;

    public $showAssignModal = false;
    public $selectedBay = '';
    public $selectedTechnician = '';
    public $technicianNotes = '';
    public $activeTab = 'job-items';

    public bool $showDebitNoteModal = false;
    public string $debitNoteNotes = '';
    public array $debitNoteItems = [];

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
                'inspector_user_id' => auth()->user()?->getAuthIdentifier(),
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

    public function getCanCreateDebitNoteProperty(): bool
    {
        return in_array($this->workOrder->status, ['in_progress', 'quality_check', 'ready']);
    }

    public function openDebitNoteModal(): void
    {
        if (!$this->canCreateDebitNote) {
            session()->flash('error', 'Debit note request can only be created when work is in progress, quality check, or ready.');
            return;
        }

        $this->showDebitNoteModal = true;
    }

    public function closeDebitNoteModal(): void
    {
        $this->resetDebitNoteForm();
    }

    public function addDebitNoteItemRow(): void
    {
        $this->debitNoteItems[] = $this->emptyDebitNoteItemRow();
    }

    public function removeDebitNoteItemRow(int $index): void
    {
        if (count($this->debitNoteItems) <= 1) {
            return;
        }

        unset($this->debitNoteItems[$index]);
        $this->debitNoteItems = array_values($this->debitNoteItems);
    }

    public function createDebitNoteRequest(): void
    {
        if (!$this->canCreateDebitNote) {
            session()->flash('error', 'Debit note request can only be created when work is in progress, quality check, or ready.');
            return;
        }

        $validated = $this->validate([
            'debitNoteNotes' => ['nullable', 'string', 'max:2000'],
            'debitNoteItems' => ['required', 'array', 'min:1'],
            'debitNoteItems.*.item_type' => ['required', 'in:labor,part'],
            'debitNoteItems.*.description' => ['required', 'string', 'max:255'],
            'debitNoteItems.*.quantity' => ['required', 'numeric', 'gt:0'],
            'debitNoteItems.*.unit_price' => ['required', 'numeric', 'min:0'],
            'debitNoteItems.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $debitNote = DebitNote::create([
                'branch_id' => $this->workOrder->branch_id,
                'work_order_id' => $this->workOrder->id,
                'customer_id' => $this->workOrder->customer_id,
                'invoice_id' => $this->workOrder->invoice?->id,
                'quotation_id' => $this->latestQuotation?->id,
                'status' => 'draft',
                'notes' => $validated['debitNoteNotes'] ?? null,
                'tax_rate' => 0,
                'discount_amount' => 0,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
            ]);

            foreach ($validated['debitNoteItems'] as $index => $row) {
                $quantity = (float) $row['quantity'];
                $unitPrice = (float) $row['unit_price'];
                $discount = (float) ($row['discount'] ?? 0);

                $debitNote->items()->create([
                    'item_type' => $row['item_type'],
                    'description' => $row['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total' => ($quantity * $unitPrice) - $discount,
                    'customer_decision' => 'pending',
                    'sort_order' => $index + 1,
                ]);
            }

            $debitNote->refresh();
            $debitNote->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        });

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
            'debitNotes.items',
        ]);

        $latestDebitNote = $this->workOrder->debitNotes->first();
        $this->resetDebitNoteForm();

        session()->flash(
            'success',
            'Debit note request created and sent. Customer review link: ' . ($latestDebitNote?->approvalUrl() ?? 'N/A')
        );
    }

    public function resendDebitNoteRequest(int $debitNoteId): void
    {
        $debitNote = $this->workOrder->debitNotes()->whereKey($debitNoteId)->firstOrFail();

        if (!in_array($debitNote->status, ['draft', 'rejected'])) {
            session()->flash('error', 'Only draft or rejected debit notes can be resent.');
            return;
        }

        $debitNote->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->workOrder->refresh();
        session()->flash('success', 'Debit note request resent. Link: ' . $debitNote->approvalUrl());
    }

    protected function resetDebitNoteForm(): void
    {
        $this->showDebitNoteModal = false;
        $this->debitNoteNotes = '';
        $this->debitNoteItems = [$this->emptyDebitNoteItemRow()];
    }

    protected function emptyDebitNoteItemRow(): array
    {
        return [
            'item_type' => 'labor',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount' => 0,
        ];
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
