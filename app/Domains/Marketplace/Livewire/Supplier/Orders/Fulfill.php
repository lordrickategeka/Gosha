<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Orders;

use App\Domains\Marketplace\Models\PurchaseOrder;
use Livewire\Component;

/**
 * STUB — supplier fulfilment view (mark shipped / partial dispatch notes) pending.
 * Buyer-side goods receipt is what actually credits stock (see PurchaseOrders\Receive).
 */
class Fulfill extends Component
{
    public PurchaseOrder $purchaseOrder;

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        $this->purchaseOrder = PurchaseOrder::asSupplier()->whereKey($purchaseOrder->id)->with('items.product', 'buyer')->firstOrFail();
    }

    public function render()
    {
        return view('livewire.marketplace.supplier.orders.fulfill');
    }
}
