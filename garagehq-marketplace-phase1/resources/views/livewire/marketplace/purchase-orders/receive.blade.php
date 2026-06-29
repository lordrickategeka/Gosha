<div class="p-6 space-y-4 max-w-3xl">
    <h1 class="text-2xl font-bold">Receive Goods — {{ $purchaseOrder->po_number }}</h1>
    <p class="text-base-content/60">From {{ $purchaseOrder->supplier?->name }}</p>

    @error('receiving') <div class="alert alert-error"><span>{{ $message }}</span></div> @enderror

    <table class="table">
        <thead><tr><th>Item</th><th>Ordered</th><th>Already received</th><th>Receiving now</th></tr></thead>
        <tbody>
            @foreach ($purchaseOrder->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? $item->description }}</td>
                    <td>{{ $item->qty_ordered }}</td>
                    <td>{{ $item->qty_received }}</td>
                    <td class="w-32">
                        <input type="number" min="0" max="{{ $item->outstandingQty() }}"
                               class="input input-bordered input-sm w-full"
                               wire:model="receiving.{{ $item->id }}" />
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <label class="form-control">
        <span class="label-text">Notes</span>
        <textarea class="textarea textarea-bordered" wire:model="notes"></textarea>
    </label>

    <div class="alert alert-info text-sm">
        <span>Confirming creates a goods receipt. Stock is credited to your inventory automatically and the PO status advances.</span>
    </div>

    <div class="flex justify-end gap-2">
        <a class="btn btn-ghost" href="{{ route('marketplace.purchase-orders.index') }}">Cancel</a>
        <button class="btn btn-primary" wire:click="receive" wire:loading.attr="disabled">Confirm receipt</button>
    </div>
</div>
