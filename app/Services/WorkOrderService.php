<?php
namespace App\Services;

use App\Models\WorkOrder;

class WorkOrderService
{
    public function consumeInventoryForWorkOrder(WorkOrder $workOrder): void
    {
        foreach ($workOrder->items()->whereNotNull('inventory_item_id')->where('inventory_consumed', false)->get() as $item) {
            $inventoryItem = $item->inventoryItem;

            if ($inventoryItem && $inventoryItem->quantity >= $item->quantity) {
                // Consume stock
                $inventoryItem->consumeForWorkOrder(
                    $workOrder,
                    $item->quantity,
                    "Used in Work Order #{$workOrder->order_number}"
                );

                // Mark as consumed
                $item->update([
                    'inventory_consumed' => true,
                    'consumed_at' => now()
                ]);
            }
        }
    }
}
