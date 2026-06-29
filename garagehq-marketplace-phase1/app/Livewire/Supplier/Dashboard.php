<?php

namespace App\Livewire\Supplier;

use App\Enums\PurchaseOrderStatus;
use App\Enums\QuoteStatus;
use App\Models\MarketplaceListing;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Rfq;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $vendorId = session('current_vendor_id') ?? auth()->user()->vendor_id;

        $openRfqs = Rfq::query()
            ->where('status', \App\Enums\RfqStatus::Published)
            ->where(function ($q) use ($vendorId) {
                $q->where('visibility', 'open')
                  ->orWhereHas('invitations', fn ($i) => $i->where('supplier_vendor_id', $vendorId));
            })
            ->count();

        return view('livewire.supplier.dashboard', [
            'activeListings' => MarketplaceListing::ownedBySupplier($vendorId)->where('is_active', true)->count(),
            'openRfqs' => $openRfqs,
            'pendingQuotes' => Quote::asSupplier($vendorId)->where('status', QuoteStatus::Submitted)->count(),
            'incomingOrders' => PurchaseOrder::asSupplier($vendorId)
                ->whereIn('status', [PurchaseOrderStatus::Sent->value, PurchaseOrderStatus::Accepted->value, PurchaseOrderStatus::Fulfilling->value])
                ->count(),
        ]);
    }
}
