<?php

namespace App\Livewire\Marketplace\PurchaseOrders;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Buyer goods receipt. Creating GoodsReceiptItem rows fires GoodsReceiptItemObserver, which
 * credits garage inventory and advances PO status — the observer-driven stock-in flow.
 *
 * Receipt qty per line is captured in $receiving keyed by purchase_order_item_id.
 */
class Receive extends Component
{
    public PurchaseOrder $purchaseOrder;

    /** [purchase_order_item_id => qty_now_receiving] */
    public array $receiving = [];

    public ?string $notes = null;

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        // Ownership: only the buyer may receive against this PO.
        $this->purchaseOrder = PurchaseOrder::asBuyer()
            ->with('items.product')
            ->whereKey($purchaseOrder->id)
            ->firstOrFail();

        foreach ($this->purchaseOrder->items as $item) {
            $this->receiving[$item->id] = $item->outstandingQty();
        }
    }

    public function receive()
    {
        $this->authorize('receive_goods');

        $lines = collect($this->receiving)
            ->map(fn ($q) => (int) $q)
            ->filter(fn ($q) => $q > 0);

        if ($lines->isEmpty()) {
            $this->addError('receiving', 'Enter at least one quantity to receive.');
            return;
        }

        DB::transaction(function () use ($lines) {
            $grn = GoodsReceipt::create([
                'reference' => 'GRN-' . now()->format('Y') . '-' . Str::padLeft((string) (GoodsReceipt::max('id') + 1), 6, '0'),
                'purchase_order_id' => $this->purchaseOrder->id,
                'branch_id' => $this->purchaseOrder->branch_id ?? session('current_branch_id'),
                'received_by' => auth()->id(),
                'received_at' => now(),
                'notes' => $this->notes,
            ]);

            foreach ($lines as $poItemId => $qty) {
                $poItem = $this->purchaseOrder->items->firstWhere('id', $poItemId);
                if (! $poItem) {
                    continue;
                }
                $qty = min($qty, $poItem->outstandingQty());
                if ($qty <= 0) {
                    continue;
                }
                // Observer handles stock increment + qty_received + PO status.
                $grn->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'qty_received' => $qty,
                ]);
            }
        });

        $this->dispatch('toast', message: 'Goods received and stock updated.');
        return $this->redirectRoute('marketplace.purchase-orders.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.marketplace.purchase-orders.receive');
    }
}
