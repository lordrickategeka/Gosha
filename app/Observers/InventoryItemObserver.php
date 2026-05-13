<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Notifications\LowStockAlert;
use App\Notifications\OutOfStockAlert;
use Illuminate\Support\Facades\Notification;

class InventoryItemObserver
{
    /**
     * Handle the InventoryItem "updated" event.
     */
    public function updated(InventoryItem $item): void
    {
        // Check if quantity changed
        if (!$item->wasChanged('quantity')) {
            return;
        }

        $oldQuantity = $item->getOriginal('quantity');
        $newQuantity = $item->quantity;

        // Alert if stock went from sufficient to low stock
        if ($oldQuantity > $item->reorder_level && $newQuantity <= $item->reorder_level && $newQuantity > 0) {
            $this->sendLowStockAlert($item);
        }

        // Alert if stock went from available to out of stock
        if ($oldQuantity > 0 && $newQuantity <= 0) {
            $this->sendOutOfStockAlert($item);
        }

        // Log significant quantity changes (threshold: more than 50% change)
        $changePercent = $oldQuantity > 0 ? abs(($newQuantity - $oldQuantity) / $oldQuantity * 100) : 100;

        if ($changePercent > 50) {
            logger()->info('Significant inventory quantity change', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'change_percent' => $changePercent,
            ]);
        }
    }

    /**
     * Send low stock alert to relevant users
     */
    protected function sendLowStockAlert(InventoryItem $item): void
    {
        try {
            // Notify branch manager and storekeeper
            $users = $item->vendor->users()
                ->where('branch_id', $item->branch_id)
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['branch_manager', 'storekeeper']))
                ->get();

            Notification::send($users, new LowStockAlert($item));
        } catch (\Exception $e) {
            logger()->error('Failed to send low stock alert', [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send out of stock alert to relevant users
     */
    protected function sendOutOfStockAlert(InventoryItem $item): void
    {
        try {
            // Notify branch manager and storekeeper with higher urgency
            $users = $item->vendor->users()
                ->where('branch_id', $item->branch_id)
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['branch_manager', 'storekeeper', 'vendor_owner']))
                ->get();

            Notification::send($users, new OutOfStockAlert($item));
        } catch (\Exception $e) {
            logger()->error('Failed to send out of stock alert', [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
