<?php

namespace App\Domains\Marketplace\Services;

use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Models\MarketplaceTransaction;
use App\Domains\Marketplace\Models\PurchaseOrder;
use App\Domains\Platform\Models\PlatformSetting;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceCommissionService
{
    // Snapshot the applicable rate at acceptance. No charge is raised until completion.
    public function meter(PurchaseOrder $po): ?MarketplaceTransaction
    {
        return DB::transaction(function () use ($po) {
            PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            if ($existing = $po->transaction()->first()) {
                return $existing;
            }
            $service = app(SelfServiceSubscriptionService::class);
            $subscription = $service->current($po->supplier);
            $rate = (float) config('marketplace.default_commission_rate', 5);
            if ($subscription?->self_service && $subscription->plan->audience === 'supplier') {
                $rate = $subscription->trial_ends_at?->isFuture() ? 0 : (float) $subscription->plan->commission_rate;
            }

            return MarketplaceTransaction::create([
                'purchase_order_id' => $po->id, 'buyer_vendor_id' => $po->buyer_vendor_id,
                'supplier_vendor_id' => $po->supplier_vendor_id, 'gross_amount' => $po->total,
                'commission_rate' => $rate, 'commission_amount' => round((float) $po->total * $rate / 100, 2),
                'currency' => $po->currency, 'status' => 'pending',
            ]);
        });
    }

    public function complete(PurchaseOrder $po): void
    {
        if ($po->status !== PurchaseOrderStatus::Completed) {
            return;
        }
        DB::transaction(function () use ($po) {
            PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            $transaction = $this->meter($po);
            if ($transaction->platform_invoice_id || $transaction->status !== 'pending') {
                return;
            }
            // Final order value may differ from its provisional accepted value.
            $amount = round((float) $po->total * (float) $transaction->commission_rate / 100, 2);
            $transaction->update(['gross_amount' => $po->total, 'commission_amount' => $amount]);
            if ($amount <= 0) {
                $transaction->update(['status' => 'waived']);

                return;
            }
            $subscription = app(SelfServiceSubscriptionService::class)->current($po->supplier);
            $invoice = VendorPlatformInvoice::create([
                'invoice_number' => 'COM-'.Str::upper(Str::random(12)),
                'vendor_id' => $po->supplier_vendor_id, 'vendor_subscription_id' => $subscription?->id,
                'type' => 'commission', 'status' => 'pending', 'currency' => $po->currency,
                'issue_date' => now(), 'due_date' => now()->addDays(7),
                'tax_amount' => round($amount * (float) PlatformSetting::get(PlatformSetting::PLATFORM_TAX_RATE, 0) / 100, 2),
                'metadata' => ['marketplace_transaction_id' => $transaction->id, 'purchase_order_id' => $po->id],
            ]);
            $invoice->addItem('Marketplace commission for '.$po->po_number, $amount, 'commission');
            $invoice->calculateTotals();
            $transaction->update(['platform_invoice_id' => $invoice->id, 'status' => 'invoiced']);
        });
    }

    public function cancel(PurchaseOrder $po): void
    {
        DB::transaction(function () use ($po) {
            PurchaseOrder::whereKey($po->id)->lockForUpdate()->firstOrFail();
            $transaction = $po->transaction()->first();
            if (! $transaction) {
                return;
            }
            $invoice = VendorPlatformInvoice::whereKey($transaction->platform_invoice_id)->lockForUpdate()->first();
            if ($invoice && $invoice->amount_paid > 0) {
                // A real payment needs operator reconciliation/refund; never claim funds were returned.
                $transaction->update(['status' => 'refund_due']);

                return;
            }
            $invoice?->update(['status' => 'cancelled']);
            $transaction->update(['status' => 'waived', 'commission_amount' => 0]);
        });
    }
}
