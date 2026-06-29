<?php

namespace App\Domains\Marketplace\Livewire\Buyer\Quotes;

use App\Domains\Marketplace\Enums\QuoteStatus;
use App\Domains\Marketplace\Models\Quote;
use App\Domains\Marketplace\Models\Rfq;
use App\Domains\Marketplace\Services\PurchaseOrderService;
use App\Domains\Marketplace\Services\RfqService;
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

        return view('livewire.marketplace.buyer.quotes.compare', compact('quotes'));
    }
}
