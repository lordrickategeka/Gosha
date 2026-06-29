<?php

namespace App\Domains\Marketplace\Services;

use App\Domains\Marketplace\Models\MarketplaceTransaction;
use App\Domains\Marketplace\Models\PurchaseOrder;

/**
 * Commission metering for the marketplace.
 *
 * One MarketplaceTransaction row is written when a PO is accepted (see PurchaseOrderObserver).
 * The commission rate is resolved from the supplier's active platform billing plan where one
 * exists, otherwise from the marketplace.default_commission_rate config fallback.
 *
 * INTEGRATION POINT: wire the two TODOs below to your BillingService API; until then it
 * degrades gracefully to the config default and leaves the transaction 'pending'.
 */
class MarketplaceCommissionService
{
    public function meter(PurchaseOrder $po): ?MarketplaceTransaction
    {
        // Idempotent: never double-meter the same PO.
        if ($po->transaction()->exists()) {
            return $po->transaction;
        }

        $rate = $this->resolveRate($po);
        $gross = (float) $po->total;
        $commission = round($gross * ($rate / 100), 2);

        $txn = MarketplaceTransaction::create([
            'purchase_order_id' => $po->id,
            'buyer_vendor_id' => $po->buyer_vendor_id,
            'supplier_vendor_id' => $po->supplier_vendor_id,
            'gross_amount' => $gross,
            'commission_rate' => $rate,
            'commission_amount' => $commission,
            'currency' => $po->currency,
            'status' => 'pending',
        ]);

        // TODO(BillingService): hand the metered amount to the platform billing pipeline, e.g.
        // app(\App\Services\BillingService::class)->recordUsage(
        //     vendorId: $po->supplier_vendor_id,
        //     type: 'marketplace_commission',
        //     amount: $commission,
        //     reference: $txn,
        // );

        return $txn;
    }

    protected function resolveRate(PurchaseOrder $po): float
    {
        // TODO(BillingService): prefer the supplier's active plan commission rate, e.g.
        // $plan = app(\App\Services\BillingService::class)->activePlanFor($po->supplier_vendor_id);
        // if ($plan && $plan->commission_rate !== null) return (float) $plan->commission_rate;

        return (float) config('marketplace.default_commission_rate', 5.0);
    }
}
