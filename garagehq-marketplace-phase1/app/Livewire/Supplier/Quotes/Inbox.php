<?php

namespace App\Livewire\Supplier\Quotes;

use App\Enums\RfqStatus;
use App\Models\Rfq;
use Livewire\Component;
use Livewire\WithPagination;

class Inbox extends Component
{
    use WithPagination;

    private function vendorId(): int
    {
        return session('current_vendor_id') ?? auth()->user()->vendor_id;
    }

    public function render()
    {
        $vendorId = $this->vendorId();

        // Open RFQs the supplier can quote on: open visibility OR explicitly invited.
        $rfqs = Rfq::query()
            ->with(['items', 'buyer'])
            ->where('status', RfqStatus::Published)
            ->where(function ($q) use ($vendorId) {
                $q->where('visibility', 'open')
                  ->orWhereHas('invitations', fn ($i) => $i->where('supplier_vendor_id', $vendorId));
            })
            ->withCount(['quotes as has_quoted' => fn ($q) => $q->where('supplier_vendor_id', $vendorId)])
            ->latest()
            ->paginate(15);

        return view('livewire.supplier.quotes.inbox', compact('rfqs'));
    }
}
