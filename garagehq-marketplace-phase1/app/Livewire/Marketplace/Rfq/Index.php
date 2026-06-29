<?php

namespace App\Livewire\Marketplace\Rfq;

use App\Models\Rfq;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lean listing of the buyer's own RFQs (asBuyer scope). Detail/edit view is a follow-up.
 */
class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $rfqs = Rfq::asBuyer()
            ->withCount('quotes')
            ->latest()
            ->paginate(15);

        return view('livewire.marketplace.rfq.index', compact('rfqs'));
    }
}
