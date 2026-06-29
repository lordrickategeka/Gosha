<?php

namespace App\Observers;

use App\Enums\PurchaseOrderStatus;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryItem;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

/**
 * STOCK-IN TRIGGER.
 *
 * When a goods receipt line is created, this observer:
 *   1. resolves (or creates) the buyer's inventory_item for the received catalog product,
 *      scoped to the buyer vendor + receiving branch,
 *   2. increments that inventory stock by the received qty,
 *   3. bumps purchase_order_items.qty_received,
 *   4. advances the PO status when fully received.
 *
 * This mirrors the existing inventory-consumption observer pattern (side effects on status/
 * lifecycle events, not in controllers/components).
 *
 * NOTE ON inventory_items columns: this assumes the existing schema has
 * `vendor_id`, `branch_id`, `catalog_product_id` (added by this package), a quantity column,
 * and a `type` discriminator. The quantity column name is read from config or defaults to
 * 'quantity' — adjust QTY_COLUMN below if your column differs (e.g. 'stock', 'qty_on_hand').
 */
class GoodsReceiptItemObserver
{
    private const QTY_COLUMN = 'quantity';

    public function created(GoodsReceiptItem $line): void
    {
        DB::transaction(function () use ($line) {
            $poItem = $line->purchaseOrderItem;
            if (! $poItem) {
                return;
            }

            $po = $poItem->purchaseOrder;

            // 1 + 2: credit garage inventory.
            $inventoryItem = $this->resolveInventoryItem($line, $poItem, $po);
            if ($inventoryItem) {
                $inventoryItem->increment(self::QTY_COLUMN, $line->qty_received);
                $line->forceFill(['inventory_item_id' => $inventoryItem->id])->saveQuietly();
            }

            // 3: track received qty on the PO line.
            $poItem->increment('qty_received', $line->qty_received);

            // 4: advance PO status.
            $po->refresh()->loadMissing('items');
            if ($po->isFullyReceived()) {
                $po->update(['status' => PurchaseOrderStatus::Received]);
            } elseif ($po->status !== PurchaseOrderStatus::Fulfilling) {
                $po->update(['status' => PurchaseOrderStatus::Fulfilling]);
            }
        });
    }

    /**
     * Find the buyer's inventory row for this catalog product at the receiving branch,
     * creating a parts row if none exists yet. Returns null if catalog product is unknown
     * (free-text PO line) — such lines simply don't auto-stock.
     */
    protected function resolveInventoryItem(GoodsReceiptItem $line, PurchaseOrderItem $poItem, $po): ?InventoryItem
    {
        if (! $poItem->catalog_product_id) {
            return null;
        }

        $branchId = $line->goodsReceipt?->branch_id ?? $po->branch_id;

        return InventoryItem::firstOrCreate(
            [
                'vendor_id' => $po->buyer_vendor_id,
                'branch_id' => $branchId,
                'catalog_product_id' => $poItem->catalog_product_id,
            ],
            [
                'name' => $poItem->product?->name ?? $poItem->description ?? 'Marketplace item',
                'type' => 'parts',
                self::QTY_COLUMN => 0,
            ]
        );
    }
}
