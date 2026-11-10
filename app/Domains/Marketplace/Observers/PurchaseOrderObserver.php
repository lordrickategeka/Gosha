<?php

namespace App\Domains\Marketplace\Observers;

use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Models\PurchaseOrder;
use App\Domains\Marketplace\Services\MarketplaceCommissionService;

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
        // Delivery and payment may arrive in either order. Both finish the sale.
        if ($po->status === PurchaseOrderStatus::Received && $po->payment_status === \App\Domains\Marketplace\Enums\PaymentStatus::Paid) {
            $po->updateQuietly(['status' => PurchaseOrderStatus::Completed]);
            app(MarketplaceCommissionService::class)->complete($po);

            return;
        }
        if (! $po->wasChanged('status')) {
            return;
        }

        if ($po->status === PurchaseOrderStatus::Accepted) {
            app(MarketplaceCommissionService::class)->meter($po);
        } elseif ($po->status === PurchaseOrderStatus::Completed) {
            app(MarketplaceCommissionService::class)->complete($po);
        } elseif ($po->status === PurchaseOrderStatus::Cancelled) {
            app(MarketplaceCommissionService::class)->cancel($po);
        }
    }
}
