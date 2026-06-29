<?php

namespace App\Observers;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Services\MarketplaceCommissionService;

/**
 * Commission metering hook.
 *
 * When a PO transitions INTO 'accepted', meter the commission exactly once. Using the
 * status-change event keeps the billing side-effect out of the Livewire components, same as
 * the rest of GarageHQ's observer-driven side effects.
 */
class PurchaseOrderObserver
{
    public function updated(PurchaseOrder $po): void
    {
        if (! $po->wasChanged('status')) {
            return;
        }

        if ($po->status === PurchaseOrderStatus::Accepted) {
            app(MarketplaceCommissionService::class)->meter($po);
        }
    }
}
