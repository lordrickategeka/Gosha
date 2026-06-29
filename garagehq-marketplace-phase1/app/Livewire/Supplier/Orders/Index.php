<?php

namespace App\Livewire\Supplier\Orders;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    private function vendorId(): int
    {
        return session('current_vendor_id') ?? auth()->user()->vendor_id;
    }

    public function accept(int $id): void
    {
        $this->authorize('manage_purchase_orders');
        $po = PurchaseOrder::asSupplier($this->vendorId())->findOrFail($id);
        // Transition into Accepted triggers commission metering via PurchaseOrderObserver.
        $po->update(['status' => PurchaseOrderStatus::Accepted]);
        $this->dispatch('toast', message: "PO {$po->po_number} accepted.");
    }

    public function render()
    {
        $orders = PurchaseOrder::asSupplier($this->vendorId())
            ->with(['buyer', 'items'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.supplier.orders.index', [
            'orders' => $orders,
            'statuses' => PurchaseOrderStatus::cases(),
        ]);
    }
}
