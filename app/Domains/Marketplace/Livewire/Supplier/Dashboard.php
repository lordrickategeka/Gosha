<?php

namespace App\Domains\Marketplace\Livewire\Supplier;

use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Enums\QuoteStatus;
use App\Domains\Marketplace\Enums\RfqStatus;
use App\Domains\Marketplace\Models\MarketplaceListing;
use App\Domains\Marketplace\Models\PurchaseOrder;
use App\Domains\Marketplace\Models\Quote;
use App\Domains\Marketplace\Models\Rfq;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $vendorId = session('current_vendor_id') ?? auth()->user()->vendor_id;

        $openRfqs = Rfq::query()
            ->where('status', RfqStatus::Published)
            ->where(function ($q) use ($vendorId) {
                $q->where('visibility', 'open')
                  ->orWhereHas('invitations', fn ($i) => $i->where('supplier_vendor_id', $vendorId));
            })
            ->count();

        return view('livewire.marketplace.supplier.dashboard', [
            'activeListings' => MarketplaceListing::ownedBySupplier($vendorId)->where('is_active', true)->count(),
            'openRfqs' => $openRfqs,
            'pendingQuotes' => Quote::asSupplier($vendorId)->where('status', QuoteStatus::Submitted)->count(),
            'incomingOrders' => PurchaseOrder::asSupplier($vendorId)
                ->whereIn('status', [PurchaseOrderStatus::Sent->value, PurchaseOrderStatus::Accepted->value, PurchaseOrderStatus::Fulfilling->value])
                ->count(),
        ]);
    }
}
