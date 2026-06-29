<?php

namespace App\Domains\Marketplace\Services;

use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Models\MarketplaceListing;
use App\Domains\Marketplace\Models\PurchaseOrder;
use App\Domains\Marketplace\Models\Quote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    /** Build a PO from an awarded RFQ quote. */
    public function createFromQuote(Quote $quote): PurchaseOrder
    {
        return DB::transaction(function () use ($quote) {
            $rfq = $quote->rfq;

            $po = PurchaseOrder::create([
                'po_number' => $this->nextPoNumber(),
                'buyer_vendor_id' => $rfq->buyer_vendor_id,
                'supplier_vendor_id' => $quote->supplier_vendor_id,
                'branch_id' => $rfq->branch_id,
                'created_by' => auth()->id(),
                'source_type' => 'rfq_quote',
                'source_id' => $quote->id,
                'currency' => $quote->currency,
                'status' => PurchaseOrderStatus::Draft,
            ]);

            foreach ($quote->items as $qi) {
                $po->items()->create([
                    'catalog_product_id' => $qi->catalog_product_id,
                    'description' => $qi->description,
                    'qty_ordered' => $qi->qty,
                    'unit_price' => $qi->unit_price,
                    'tax_rate' => $qi->tax_rate,
                ]);
            }

            $this->recalculate($po);

            return $po->refresh();
        });
    }

    /** Build a PO from a direct listing purchase. */
    public function createFromListing(MarketplaceListing $listing, int $qty, int $buyerVendorId, ?int $branchId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($listing, $qty, $buyerVendorId, $branchId) {
            $po = PurchaseOrder::create([
                'po_number' => $this->nextPoNumber(),
                'buyer_vendor_id' => $buyerVendorId,
                'supplier_vendor_id' => $listing->supplier_vendor_id,
                'branch_id' => $branchId,
                'created_by' => auth()->id(),
                'source_type' => 'direct_listing',
                'source_id' => $listing->id,
                'currency' => $listing->currency,
                'status' => PurchaseOrderStatus::Draft,
            ]);

            $po->items()->create([
                'catalog_product_id' => $listing->catalog_product_id,
                'description' => $listing->product?->name,
                'qty_ordered' => $qty,
                'unit_price' => $listing->priceForQty($qty),
                'tax_rate' => 0,
            ]);

            $this->recalculate($po);

            return $po->refresh();
        });
    }

    public function recalculate(PurchaseOrder $po): void
    {
        $po->loadMissing('items');
        $subtotal = $po->items->sum(fn ($i) => $i->qty_ordered * (float) $i->unit_price);
        $tax = $po->items->sum(fn ($i) => $i->qty_ordered * (float) $i->unit_price * ((float) $i->tax_rate / 100));
        $po->forceFill([
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($tax, 2),
            'total' => round($subtotal + $tax, 2),
        ])->save();
    }

    public function nextPoNumber(): string
    {
        return 'PO-' . now()->format('Y') . '-' . Str::padLeft((string) (PurchaseOrder::max('id') + 1), 6, '0');
    }
}
