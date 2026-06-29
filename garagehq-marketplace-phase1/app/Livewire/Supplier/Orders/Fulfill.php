<?php

namespace App\Livewire\Supplier\Orders;

use App\Models\PurchaseOrder;
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
        $this->purchaseOrder = $purchaseOrder->load('items.product', 'buyer');
    }

    public function render()
    {
        return view('livewire.supplier.orders.fulfill');
    }
}
