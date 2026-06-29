<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Quotes;

use App\Domains\Marketplace\Models\Rfq;
use Livewire\Component;

/**
 * STUB — full quote builder pending. Loads the RFQ context so the route resolves and the
 * supplier can see what they'd be quoting. Line-item entry + Quote::recalculateTotals() wiring
 * is the next implementation step.
 */
class Compose extends Component
{
    public Rfq $rfq;

    public function mount(Rfq $rfq): void
    {
        $this->rfq = $rfq->load('items.product');
    }

    public function render()
    {
        return view('livewire.marketplace.supplier.quotes.compose');
    }
}
