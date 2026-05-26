<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\WorkOrder;
use App\Models\WashOrder;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Consume inventory for a completed work order
     */
    public function consumeWorkOrderInventory(WorkOrder $workOrder): void
    {
        DB::transaction(function () use ($workOrder) {
            // Get all work order items that have inventory linked and aren't consumed yet
            $items = $workOrder->items()
                ->whereNotNull('inventory_item_id')
                ->where('inventory_consumed', false)
                ->get();

            foreach ($items as $item) {
                $inventoryItem = $item->inventoryItem;

                if (!$inventoryItem) {
                    continue;
                }

                // Check if sufficient stock
                if ($inventoryItem->quantity < $item->quantity) {
                    logger()->warning('Insufficient inventory for work order item', [
                        'work_order_id' => $workOrder->id,
                        'inventory_item_id' => $inventoryItem->id,
                        'required' => $item->quantity,
                        'available' => $inventoryItem->quantity,
                    ]);
                    continue;
                }

                // Consume the stock
                $this->consumeStock(
                    inventoryItem: $inventoryItem,
                    quantity: $item->quantity,
                    movableType: WorkOrder::class,
                    movableId: $workOrder->id,
                    branchId: $workOrder->branch_id,
                    notes: "Consumed for Work Order #{$workOrder->order_number}"
                );

                // Mark work order item as consumed
                $item->update([
                    'inventory_consumed' => true,
                    'consumed_at' => now(),
                ]);
            }
        });
    }

    /**
     * Consume inventory for a completed wash order
     */
    public function consumeWashOrderInventory(WashOrder $washOrder): void
    {
        DB::transaction(function () use ($washOrder) {
            // Option 1: Explicit wash order items with inventory links
            $items = $washOrder->items()
                ->whereNotNull('inventory_item_id')
                ->where('inventory_consumed', false)
                ->get();

            foreach ($items as $item) {
                $inventoryItem = $item->inventoryItem;

                if (!$inventoryItem) {
                    continue;
                }

                // Use quantity_used if specified, otherwise use usage_rate from inventory item
                $quantityToConsume = $item->quantity_used
                    ?? $inventoryItem->usage_rate
                    ?? 1;

                if ($inventoryItem->quantity < $quantityToConsume) {
                    logger()->warning('Insufficient inventory for wash order item', [
                        'wash_order_id' => $washOrder->id,
                        'inventory_item_id' => $inventoryItem->id,
                        'required' => $quantityToConsume,
                        'available' => $inventoryItem->quantity,
                    ]);
                    continue;
                }

                $this->consumeStock(
                    inventoryItem: $inventoryItem,
                    quantity: $quantityToConsume,
                    movableType: WashOrder::class,
                    movableId: $washOrder->id,
                    branchId: $washOrder->branch_id,
                    notes: "Consumed for Wash Order #{$washOrder->order_number}"
                );

                $item->update([
                    'inventory_consumed' => true,
                    'consumed_at' => now(),
                ]);
            }

            // Option 2: Auto-consume standard wash supplies based on wash package
            // (if you have a wash_packages table with predefined supply lists)
            if ($washOrder->package_id) {
                $this->consumeStandardWashSupplies($washOrder);
            }
        });
    }

    /**
     * Consume stock and create movement record
     */
    protected function consumeStock(
        InventoryItem $inventoryItem,
        float $quantity,
        string $movableType,
        int $movableId,
        int $branchId,
        ?string $notes = null
    ): InventoryMovement {
        // Create movement record
        $movement = InventoryMovement::create([
            'inventory_item_id' => $inventoryItem->id,
            'branch_id' => $branchId,
            'movement_type' => $movableType === WorkOrder::class
                ? 'work_order_use'
                : 'wash_order_use',
            'quantity_change' => -$quantity,
            'quantity_after' => $inventoryItem->quantity - $quantity,
            'unit_cost' => $inventoryItem->cost_price,
            'total_cost' => $quantity * $inventoryItem->cost_price,
            'movable_type' => $movableType,
            'movable_id' => $movableId,
            'performed_by' => auth()->id() ?? 1, // System user if no auth
            'notes' => $notes,
            'movement_date' => now(),
        ]);

        // Decrement quantity
        $inventoryItem->decrement('quantity', $quantity);

        return $movement;
    }

    /**
     * Auto-consume standard wash supplies based on package
     */
    protected function consumeStandardWashSupplies(WashOrder $washOrder): void
    {
        // Implementation depends on your wash_packages structure
        // Example:
        // $package = $washOrder->package;
        // foreach ($package->supplies as $supply) {
        //     $inventoryItem = InventoryItem::find($supply->inventory_item_id);
        //     if ($inventoryItem && $inventoryItem->quantity >= $supply->quantity) {
        //         $this->consumeStock(...);
        //     }
        // }
    }

    /**
     * Add stock from purchase/receipt
     */
    public function addStock(
        InventoryItem $inventoryItem,
        float $quantity,
        ?int $supplierId = null,
        ?float $unitCost = null,
        ?string $notes = null
    ): InventoryMovement {
        $actualUnitCost = $unitCost ?? $inventoryItem->cost_price;

        $movement = InventoryMovement::create([
            'inventory_item_id' => $inventoryItem->id,
            'branch_id' => $inventoryItem->branch_id,
            'movement_type' => 'purchase',
            'quantity_change' => $quantity,
            'quantity_after' => $inventoryItem->quantity + $quantity,
            'unit_cost' => $actualUnitCost,
            'total_cost' => $quantity * $actualUnitCost,
            'supplier_id' => $supplierId,
            'performed_by' => auth()->id(),
            'notes' => $notes,
            'movement_date' => now(),
        ]);

        $inventoryItem->increment('quantity', $quantity);

        // Update cost price if different
        if ($unitCost && $unitCost !== $inventoryItem->cost_price) {
            $inventoryItem->update(['cost_price' => $unitCost]);
        }

        return $movement;
    }

    /**
     * Adjust stock (manual correction)
     */
    public function adjustStock(
        InventoryItem $inventoryItem,
        float $quantityChange,
        string $reason
    ): InventoryMovement {
        $movementType = $quantityChange > 0 ? 'adjustment_in' : 'adjustment_out';

        $movement = InventoryMovement::create([
            'inventory_item_id' => $inventoryItem->id,
            'branch_id' => $inventoryItem->branch_id,
            'movement_type' => $movementType,
            'quantity_change' => $quantityChange,
            'quantity_after' => $inventoryItem->quantity + $quantityChange,
            'performed_by' => auth()->id(),
            'notes' => $reason,
            'movement_date' => now(),
        ]);

        $inventoryItem->increment('quantity', $quantityChange);

        return $movement;
    }

    /**
     * Reserve stock for a movable (work order / wash order) without decrementing actual quantity.
     */
    public function reserveStock(
        InventoryItem $inventoryItem,
        float $quantity,
        string $movableType,
        int $movableId,
        int $branchId,
        ?string $notes = null
    ): InventoryMovement {
        // Ensure available stock
        $available = $inventoryItem->quantity - ($inventoryItem->reserved_quantity ?? 0);
        if ($quantity > $available) {
            // Do not reserve beyond available; throw or return null behavior — we'll log and throw
            throw new \InvalidArgumentException('Insufficient available stock to reserve.');
        }

        // Create reservation movement (does not change actual quantity)
        $movement = InventoryMovement::create([
            'inventory_item_id' => $inventoryItem->id,
            'branch_id' => $branchId,
            'movement_type' => 'reservation',
            'quantity_change' => 0,
            'quantity_after' => $inventoryItem->quantity,
            'movable_type' => $movableType,
            'movable_id' => $movableId,
            'performed_by' => auth()->id(),
            'notes' => ($notes ?? '') . " Reserved {$quantity}",
            'movement_date' => now(),
        ]);

        // Increment reserved quantity
        $inventoryItem->reserve($quantity);

        return $movement;
    }

    /**
     * Release a reservation previously held on an inventory item
     */
    public function releaseReservation(
        InventoryItem $inventoryItem,
        float $quantity,
        string $movableType = null,
        int $movableId = null,
        int $branchId = null,
        ?string $notes = null
    ): InventoryMovement {
        // Decrement reserved quantity (not below zero)
        $inventoryItem->releaseReservation($quantity);

        $movement = InventoryMovement::create([
            'inventory_item_id' => $inventoryItem->id,
            'branch_id' => $branchId ?? $inventoryItem->branch_id,
            'movement_type' => 'reservation_release',
            'quantity_change' => 0,
            'quantity_after' => $inventoryItem->quantity,
            'movable_type' => $movableType,
            'movable_id' => $movableId,
            'performed_by' => auth()->id(),
            'notes' => ($notes ?? '') . " Released {$quantity}",
            'movement_date' => now(),
        ]);

        return $movement;
    }

    /**
     * Release all reservations for a given work order (used when quotation rejected/cancelled)
     */
    public function releaseReservationsForWorkOrder(\App\Models\WorkOrder $workOrder): void
    {
        foreach ($workOrder->items()->whereNotNull('inventory_item_id')->get() as $item) {
            $inventoryItem = $item->inventoryItem;
            if (! $inventoryItem) {
                continue;
            }

            $quantity = $item->quantity ?? 0;
            if ($quantity <= 0) {
                continue;
            }

            try {
                $this->releaseReservation($inventoryItem, $quantity, \App\Models\WorkOrder::class, $workOrder->id, $workOrder->branch_id, "Release reservation due to quotation rejection/cancel");
            } catch (\Throwable $e) {
                logger()->error('Failed to release reservation', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Transfer stock between branches
     */
    public function transferStock(
        InventoryItem $inventoryItem,
        float $quantity,
        int $fromBranchId,
        int $toBranchId,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($inventoryItem, $quantity, $fromBranchId, $toBranchId, $notes) {
            // Create outgoing movement
            $outMovement = InventoryMovement::create([
                'inventory_item_id' => $inventoryItem->id,
                'branch_id' => $fromBranchId,
                'movement_type' => 'transfer_out',
                'quantity_change' => -$quantity,
                'quantity_after' => $inventoryItem->quantity - $quantity,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'performed_by' => auth()->id(),
                'notes' => $notes,
                'movement_date' => now(),
            ]);

            // Find or create inventory item at destination branch
            $destinationItem = InventoryItem::firstOrCreate(
                [
                    'vendor_id' => $inventoryItem->vendor_id,
                    'branch_id' => $toBranchId,
                    'sku' => $inventoryItem->sku,
                ],
                $inventoryItem->only([
                    'category_id', 'supplier_id', 'item_type', 'name', 'brand',
                    'oem_number', 'aftermarket_number', 'position', 'condition',
                    'unit', 'unit_of_measure', 'cost_price', 'selling_price',
                    'reorder_level', 'description'
                ]) + ['quantity' => 0]
            );

            // Create incoming movement at destination
            $inMovement = InventoryMovement::create([
                'inventory_item_id' => $destinationItem->id,
                'branch_id' => $toBranchId,
                'movement_type' => 'transfer_in',
                'quantity_change' => $quantity,
                'quantity_after' => $destinationItem->quantity + $quantity,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'performed_by' => auth()->id(),
                'notes' => $notes,
                'movement_date' => now(),
            ]);

            // Update quantities
            $inventoryItem->decrement('quantity', $quantity);
            $destinationItem->increment('quantity', $quantity);

            return [
                'out_movement' => $outMovement,
                'in_movement' => $inMovement,
                'destination_item' => $destinationItem,
            ];
        });
    }
}
