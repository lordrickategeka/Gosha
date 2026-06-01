<?php

namespace App\Livewire\WorkOrders;

use App\Models\PartInstallationHistory;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\WorkOrderPartSource;
use App\Services\PartsIntelligence\PartRecommendationService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PartsIntelligencePanel extends Component
{
    public WorkOrder $workOrder;

    public array $recommendations = [];
    public ?int $selectedWorkOrderItemId = null;

    public bool $showSourceModal = false;

    public ?int $sourceWorkOrderItemId = null;
    public ?int $sourceSupplierId = null;
    public ?string $sourceName = null;
    public ?string $sourceLink = null;
    public ?string $sourcePartNumber = null;
    public ?float $sourceSupplierPrice = null;
    public ?float $sourceShippingCost = 0;
    public ?float $sourceDutyCost = 0;
    public ?float $sourceClearingCost = 0;
    public ?float $sourceMarginAmount = 0;
    public ?float $sourceMarginPercent = null;
    public bool $sourceIsLocal = false;
    public ?string $sourceAvailability = null;
    public ?int $sourceLeadTimeDays = null;
    public ?string $sourceWarrantyText = null;
    public ?string $sourceNotes = null;

    public bool $showInstallationModal = false;

    public ?int $installationWorkOrderItemId = null;
    public ?int $installationSourceId = null;
    public ?string $installationFitStatus = 'unknown';
    public ?int $installationTechnicianId = null;
    public ?string $installationInstalledAt = null;
    public ?bool $installationWasReturned = false;
    public ?string $installationNotes = null;
    public ?string $installationFailureReason = null;

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load([
            'items',
            'items.partSources.supplier',
            'items.installationHistory',
        ]);
    }

    public function openSourceModal(int $workOrderItemId): void
    {
        $this->resetSourceForm();
        $this->sourceWorkOrderItemId = $workOrderItemId;
        $this->showSourceModal = true;
    }

    public function closeSourceModal(): void
    {
        $this->showSourceModal = false;
        $this->resetSourceForm();
    }

    public function saveSource(): void
    {
        $validated = $this->validate([
            'sourceWorkOrderItemId' => [
                'required',
                'integer',
                Rule::exists('work_order_items', 'id'),
            ],
            'sourceSupplierId' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'sourceName' => ['nullable', 'string', 'max:255'],
            'sourceLink' => ['nullable', 'url', 'max:2048'],
            'sourcePartNumber' => ['nullable', 'string', 'max:100'],
            'sourceSupplierPrice' => ['nullable', 'numeric', 'min:0'],
            'sourceShippingCost' => ['nullable', 'numeric', 'min:0'],
            'sourceDutyCost' => ['nullable', 'numeric', 'min:0'],
            'sourceClearingCost' => ['nullable', 'numeric', 'min:0'],
            'sourceMarginAmount' => ['nullable', 'numeric', 'min:0'],
            'sourceMarginPercent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sourceAvailability' => ['nullable', 'string', 'max:50'],
            'sourceLeadTimeDays' => ['nullable', 'integer', 'min:0'],
            'sourceWarrantyText' => ['nullable', 'string', 'max:255'],
            'sourceNotes' => ['nullable', 'string'],
        ]);

        WorkOrderPartSource::create([
            'work_order_item_id' => $validated['sourceWorkOrderItemId'],
            'supplier_id' => $validated['sourceSupplierId'] ?? null,
            'source_name' => $validated['sourceName'] ?? null,
            'source_link' => $validated['sourceLink'] ?? null,
            'source_part_number' => $validated['sourcePartNumber'] ?? null,
            'supplier_price' => $validated['sourceSupplierPrice'] ?? null,
            'shipping_cost' => $validated['sourceShippingCost'] ?? 0,
            'duty_cost' => $validated['sourceDutyCost'] ?? 0,
            'clearing_cost' => $validated['sourceClearingCost'] ?? 0,
            'margin_amount' => $validated['sourceMarginAmount'] ?? 0,
            'margin_percent' => $validated['sourceMarginPercent'] ?? null,
            'is_local' => $this->sourceIsLocal,
            'availability' => $validated['sourceAvailability'] ?? null,
            'lead_time_days' => $validated['sourceLeadTimeDays'] ?? null,
            'warranty_text' => $validated['sourceWarrantyText'] ?? null,
            'notes' => $validated['sourceNotes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->refreshWorkOrder();
        $this->closeSourceModal();

        session()->flash('success', 'Part source saved successfully.');
    }

    public function getRecommendation(int $workOrderItemId): void
    {
        $item = WorkOrderItem::query()
            ->with(['workOrder.vehicle.profile'])
            ->findOrFail($workOrderItemId);

        $service = app(PartRecommendationService::class);
        $result = $service->recommend($item);

        $this->selectedWorkOrderItemId = $workOrderItemId;
        $this->recommendations[$workOrderItemId] = $result;

        if (!empty($result['recommended'])) {
            session()->flash('success', 'Recommendation generated.');
        } else {
            session()->flash('warning', 'No supplier candidates found for recommendation.');
        }
    }

    public function markRecommended(int $sourceId): void
    {
        $source = WorkOrderPartSource::with('workOrderItem.workOrder.vehicle')->findOrFail($sourceId);

        WorkOrderPartSource::where('work_order_item_id', $source->work_order_item_id)
            ->update(['is_recommended' => false]);

        $source->update(['is_recommended' => true]);

        // Persist recommendation snapshot for workflow traceability (Step 6)
        $workOrderItem = $source->workOrderItem;
        if ($workOrderItem && $workOrderItem->workOrder) {
            PartInstallationHistory::create([
                'work_order_id' => $workOrderItem->work_order_id,
                'work_order_item_id' => $workOrderItem->getKey(),
                'vehicle_id' => $workOrderItem->workOrder->vehicle_id,
                'work_order_part_source_id' => $source->getKey(),
                'supplier_part_id' => $source->supplier_part_id,
                'inventory_item_id' => $workOrderItem->inventory_item_id,
                'installed_at' => now(),
                'fit_status' => 'unknown',
                'fitment_notes' => 'Recommendation snapshot committed.',
                'recorded_by' => auth()->id(),
            ]);
        }

        $this->refreshWorkOrder();

        session()->flash('success', 'Recommended source selected and snapshot saved.');
    }

    public function getPartItemsProperty()
    {
        return $this->workOrder->items
            ->where('item_type', 'part')
            ->values();
    }

    public function getSuppliersProperty()
    {
        return Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function openInstallationModal(int $workOrderItemId, ?int $sourceId = null): void
    {
        $this->resetInstallationForm();

        $this->installationWorkOrderItemId = $workOrderItemId;
        $this->installationSourceId = $sourceId;
        $this->installationInstalledAt = now()->format('Y-m-d\TH:i');
        $this->showInstallationModal = true;
    }

    public function closeInstallationModal(): void
    {
        $this->showInstallationModal = false;
        $this->resetInstallationForm();
    }

    public function saveInstallationOutcome(): void
    {
        $validated = $this->validate([
            'installationWorkOrderItemId' => ['required', 'integer', Rule::exists('work_order_items', 'id')],
            'installationSourceId' => ['nullable', 'integer', Rule::exists('work_order_part_sources', 'id')],
            'installationFitStatus' => ['required', Rule::in(['fitted_ok', 'failed', 'modified', 'unknown'])],
            'installationTechnicianId' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'installationInstalledAt' => ['nullable', 'date'],
            'installationWasReturned' => ['nullable', 'boolean'],
            'installationNotes' => ['nullable', 'string'],
            'installationFailureReason' => ['nullable', 'string'],
        ]);

        $item = WorkOrderItem::with('workOrder')->findOrFail($validated['installationWorkOrderItemId']);
        $source = null;

        if (!empty($validated['installationSourceId'])) {
            $source = WorkOrderPartSource::find($validated['installationSourceId']);
        }

        PartInstallationHistory::create([
            'work_order_id' => $item->work_order_id,
            'work_order_item_id' => (int) $validated['installationWorkOrderItemId'],
            'vehicle_id' => $item->workOrder->vehicle_id,
            'work_order_part_source_id' => isset($validated['installationSourceId']) ? (int) $validated['installationSourceId'] : null,
            'supplier_part_id' => $source?->supplier_part_id,
            'inventory_item_id' => $item->inventory_item_id,
            'technician_id' => $validated['installationTechnicianId'] ?? null,
            'installed_at' => $validated['installationInstalledAt'] ?? now(),
            'fit_status' => $validated['installationFitStatus'],
            'was_returned' => (bool) ($validated['installationWasReturned'] ?? false),
            'fitment_notes' => $validated['installationNotes'] ?? null,
            'failure_reason' => $validated['installationFailureReason'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        $this->refreshWorkOrder();
        $this->closeInstallationModal();

        session()->flash('success', 'Installation outcome saved successfully.');
    }

    public function getTechniciansProperty()
    {
        return User::role('technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function refreshWorkOrder(): void
    {
        $this->workOrder = $this->workOrder->fresh([
            'items',
            'items.partSources.supplier',
            'items.installationHistory',
        ]);
    }

    private function resetSourceForm(): void
    {
        $this->sourceWorkOrderItemId = null;
        $this->sourceSupplierId = null;
        $this->sourceName = null;
        $this->sourceLink = null;
        $this->sourcePartNumber = null;
        $this->sourceSupplierPrice = null;
        $this->sourceShippingCost = 0;
        $this->sourceDutyCost = 0;
        $this->sourceClearingCost = 0;
        $this->sourceMarginAmount = 0;
        $this->sourceMarginPercent = null;
        $this->sourceIsLocal = false;
        $this->sourceAvailability = null;
        $this->sourceLeadTimeDays = null;
        $this->sourceWarrantyText = null;
        $this->sourceNotes = null;
    }

    private function resetInstallationForm(): void
    {
        $this->installationWorkOrderItemId = null;
        $this->installationSourceId = null;
        $this->installationFitStatus = 'unknown';
        $this->installationTechnicianId = null;
        $this->installationInstalledAt = null;
        $this->installationWasReturned = false;
        $this->installationNotes = null;
        $this->installationFailureReason = null;
    }

    public function render()
    {
        return view('livewire.work-orders.parts-intelligence-panel');
    }
}
