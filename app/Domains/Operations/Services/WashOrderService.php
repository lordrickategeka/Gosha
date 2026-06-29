<?php
namespace App\Domains\Operations\Services;

use App\Domains\Operations\Models\WashOrder;

class WashOrderService
{
    public function consumeInventoryForWashOrder(WashOrder $washOrder): void
    {
        foreach ($washOrder->items()->whereNotNull('inventory_item_id')->where('inventory_consumed', false)->get() as $item) {
            $inventoryItem = $item->inventoryItem;

            $quantityToConsume = $item->quantity_used ?? $inventoryItem->usage_rate ?? 1;

            if ($inventoryItem && $inventoryItem->quantity >= $quantityToConsume) {
                $inventoryItem->consumeForWashOrder(
                    $washOrder,
                    $quantityToConsume,
                    "Used in Wash Order #{$washOrder->order_number}"
                );

                $item->update([
                    'inventory_consumed' => true,
                    'consumed_at' => now()
                ]);
            }
        }
    }
}
