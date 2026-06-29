<?php

namespace App\Domains\Marketplace\Services;

use App\Domains\Marketplace\Enums\QuoteStatus;
use App\Domains\Marketplace\Enums\RfqStatus;
use App\Domains\Marketplace\Models\Quote;
use App\Domains\Marketplace\Models\Rfq;
use App\Domains\Marketplace\Models\RfqInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RfqService
{
    /** Publish a draft RFQ, fanning out invitations for targeted RFQs. */
    public function publish(Rfq $rfq, array $supplierVendorIds = []): Rfq
    {
        return DB::transaction(function () use ($rfq, $supplierVendorIds) {
            $rfq->update(['status' => RfqStatus::Published]);

            if (! $rfq->isOpen()) {
                foreach (array_unique($supplierVendorIds) as $sid) {
                    RfqInvitation::firstOrCreate(
                        ['rfq_id' => $rfq->id, 'supplier_vendor_id' => $sid],
                        ['status' => 'invited']
                    );
                }
            }

            return $rfq->refresh();
        });
    }

    /**
     * Award a quote. Marks the winning quote Awarded, rejects the rest, closes the RFQ,
     * and hands off to PurchaseOrderService to materialise the PO.
     */
    public function award(Quote $quote, PurchaseOrderService $poService): \App\Domains\Marketplace\Models\PurchaseOrder
    {
        return DB::transaction(function () use ($quote, $poService) {
            $rfq = $quote->rfq;

            $quote->update(['status' => QuoteStatus::Awarded]);

            Quote::where('rfq_id', $rfq->id)
                ->where('id', '!=', $quote->id)
                ->whereIn('status', [QuoteStatus::Draft->value, QuoteStatus::Submitted->value])
                ->update(['status' => QuoteStatus::Rejected->value]);

            $rfq->update(['status' => RfqStatus::Awarded]);

            return $poService->createFromQuote($quote);
        });
    }

    public function nextReference(): string
    {
        return 'RFQ-' . now()->format('Y') . '-' . Str::padLeft((string) (Rfq::max('id') + 1), 6, '0');
    }
}
