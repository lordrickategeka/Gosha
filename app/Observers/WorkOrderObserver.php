<?php

namespace App\Observers;

use App\Models\QualityCheck;
use App\Models\QualityCheckTemplate;
use App\Models\WorkOrder;
use App\Services\InventoryService;

class WorkOrderObserver
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Handle the WorkOrder "updating" event.
     */
    public function updating(WorkOrder $workOrder): void
    {
        // Check if status is changing to quality_check
        if ($workOrder->isDirty('status') && $workOrder->status === 'quality_check') {
            // Auto-create quality check record if it doesn't exist
            $qualityCheck = $workOrder->qualityCheck;

            if (!$qualityCheck) {
                $qualityCheck = QualityCheck::create([
                    'work_order_id' => $workOrder->id,
                    'vehicle_id' => $workOrder->vehicle_id,
                    'customer_id' => $workOrder->customer_id,
                    'vendor_id' => $workOrder->vendor_id,
                    'branch_id' => $workOrder->branch_id,
                    'inspector_user_id' => auth()->id(),
                    'status' => 'pending',
                    'inspection_date' => today(),
                ]);
            }

            // Ensure checklist items exist even for previously created empty quality checks.
            if (!$qualityCheck->items()->exists()) {
                $this->createChecklistItemsFromTemplate($qualityCheck, $workOrder);
            }
        }
    }

    /**
     * Handle the WorkOrder "updated" event.
     */
    public function updated(WorkOrder $workOrder): void
    {
        // ✅ NEW: Consume inventory when work order is completed
        if ($workOrder->wasChanged('status') && $workOrder->status === 'completed') {
            $this->consumeInventoryForCompletedOrder($workOrder);
        }

        // ✅ NEW: If work order is combo and marked ready, trigger wash order creation
        if ($workOrder->wasChanged('status') && $workOrder->status === 'ready' && $workOrder->is_combo) {
            $this->createWashOrderForCombo($workOrder);
        }
    }

    /**
     * Consume inventory items when work order completes
     */
    protected function consumeInventoryForCompletedOrder(WorkOrder $workOrder): void
    {
        try {
            $this->inventoryService->consumeWorkOrderInventory($workOrder);
        } catch (\Exception $e) {
            // Log error but don't block order completion
            logger()->error('Failed to consume inventory for work order', [
                'work_order_id' => $workOrder->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create wash order for combo workflow
     */
    protected function createWashOrderForCombo(WorkOrder $workOrder): void
    {
        // Check if wash order already exists
        if ($workOrder->washOrder()->exists()) {
            return;
        }

        try {
            // Create wash order automatically
            $workOrder->washOrder()->create([
                'branch_id' => $workOrder->branch_id,
                'customer_id' => $workOrder->customer_id,
                'vehicle_id' => $workOrder->vehicle_id,
                'work_order_id' => $workOrder->id,
                'wash_type' => 'standard',
                'status' => 'queued',
                'source' => 'combo',
                'priority' => 'normal',
                'notes' => "Auto-created from Work Order #{$workOrder->order_number}",
                'queued_at' => now(),
            ]);
        } catch (\Exception $e) {
            logger()->error('Failed to create wash order for combo', [
                'work_order_id' => $workOrder->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create quality check items from template
     */
    protected function createChecklistItemsFromTemplate(QualityCheck $qualityCheck, WorkOrder $workOrder): void
    {
        $templates = QualityCheckTemplate::getForVendor($workOrder->vendor_id);

        // Check if any service requires road test
        try {
            $requiresRoadTest = $workOrder->items()
                ->where('description', 'like', '%road test%')
                ->exists();
        } catch (\Exception $e) {
            $requiresRoadTest = false;
        }

        foreach ($templates as $section => $items) {
            // Skip road test section if not required
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
    }
}
