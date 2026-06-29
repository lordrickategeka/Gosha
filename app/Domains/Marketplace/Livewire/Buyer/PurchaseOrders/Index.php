<?php

namespace App\Domains\Marketplace\Livewire\Buyer\PurchaseOrders;

use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function send(int $id): void
    {
        $this->authorize('manage_purchase_orders');
        $po = PurchaseOrder::asBuyer()->findOrFail($id);
        if ($po->status === PurchaseOrderStatus::Draft) {
            $po->update(['status' => PurchaseOrderStatus::Sent]);
            $this->dispatch('toast', message: "PO {$po->po_number} sent to supplier.");
        }
    }

    public function render()
    {
        $orders = PurchaseOrder::asBuyer()
            ->with(['supplier', 'items'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.marketplace.buyer.purchase-orders.index', [
            'orders' => $orders,
            'statuses' => PurchaseOrderStatus::cases(),
        ]);
    }
}
