<?php

namespace App\Livewire\Marketplace\Quotes;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Models\Rfq;
use App\Services\PurchaseOrderService;
use App\Services\RfqService;
use Livewire\Component;

/**
 * Buyer-side quote comparison + award for one RFQ.
 *
 * The RFQ is loaded via asBuyer() so a vendor can only compare quotes on their OWN RFQs —
 * the cross-tenant boundary held explicitly rather than by a global scope. Awarding hands off
 * to RfqService::award(), which rejects rival quotes, closes the RFQ, and materialises a PO.
 */
class Compare extends Component
{
    public Rfq $rfq;

    public function mount(Rfq $rfq): void
    {
        // Enforce ownership: throws 404 if the current vendor isn't the buyer.
        $this->rfq = Rfq::asBuyer()->whereKey($rfq->id)->firstOrFail();
    }

    public function award(int $quoteId, RfqService $rfqService, PurchaseOrderService $poService)
    {
        $this->authorize('award_quotes');

        $quote = Quote::where('rfq_id', $this->rfq->id)
            ->where('status', QuoteStatus::Submitted)
            ->findOrFail($quoteId);

        $po = $rfqService->award($quote, $poService);

        $this->dispatch('toast', message: "Quote awarded — draft PO {$po->po_number} created.");
        return $this->redirectRoute('marketplace.purchase-orders.index', navigate: true);
    }

    public function render()
    {
        $quotes = $this->rfq->quotes()
            ->where('status', QuoteStatus::Submitted)
            ->with(['supplier', 'items'])
            ->orderBy('total')
            ->get();

        return view('livewire.marketplace.quotes.compare', compact('quotes'));
    }
}
