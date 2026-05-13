<?php

namespace App\Observers;

use App\Enums\WashOrderStatus;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\WashOrder;
use App\Models\QualityCheck;
use App\Models\WorkOrder;
use App\Services\InventoryService;

class WashOrderObserver
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * When a wash order transitions to 'completed', automatically:
     * 1. Consume wash supplies inventory
     * 2. Create commissions for the assigned attendant
     */
    public function updated(WashOrder $washOrder): void
    {
        if (!$washOrder->wasChanged('status')) {
            return;
        }

        $newStatus = $washOrder->status instanceof WashOrderStatus
            ? $washOrder->status
            : WashOrderStatus::tryFrom($washOrder->status);

        if ($newStatus !== WashOrderStatus::Completed) {
            return;
        }

        // ✅ NEW: Consume wash supplies inventory
        $this->consumeInventoryForCompletedOrder($washOrder);

        // Existing: Create commission
        $this->createCommissionForAttendant($washOrder);
    }

    /**
     * Consume wash supplies inventory when wash order completes
     */
    protected function consumeInventoryForCompletedOrder(WashOrder $washOrder): void
    {
        try {
            $this->inventoryService->consumeWashOrderInventory($washOrder);
        } catch (\Exception $e) {
            // Log error but don't block order completion
            logger()->error('Failed to consume inventory for wash order', [
                'wash_order_id' => $washOrder->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create commission for wash attendant
     */
    protected function createCommissionForAttendant(WashOrder $washOrder): void
    {
        if (!$washOrder->assigned_attendant_id) {
            return;
        }

        $attendant = $washOrder->assignedAttendant;
        if (!$attendant) {
            return;
        }

        $rule = CommissionRule::where('vendor_id', $attendant->vendor_id)
            ->where('branch_id', $washOrder->branch_id)
            ->where('role', 'wash_attendant')
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            return;
        }

        // Avoid double-creating commissions
        $alreadyExists = Commission::where('reference_type', 'wash_order')
            ->where('reference_id', $washOrder->id)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        Commission::createFromWashOrder($washOrder, $attendant, $rule);
    }
}
