<div class="gh-page" style="max-width:52rem;">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Receive Goods — {{ $purchaseOrder->po_number }}</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">From {{ $purchaseOrder->supplier?->name }}</p>
    </div>

    @error('receiving')
        <div class="gh-badge gh-badge--error" style="display:block; padding:10px 12px; font-size:12px;">{{ $message }}</div>
    @enderror

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Item</th><th>Ordered</th><th>Already received</th><th>Receiving now</th></tr></thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $item)
                        <tr>
                            <td style="font-weight:700;">{{ $item->product?->name ?? $item->description }}</td>
                            <td class="gh-muted">{{ $item->qty_ordered }}</td>
                            <td class="gh-muted">{{ $item->qty_received }}</td>
                            <td style="width:8rem;">
                                <input type="number" min="0" max="{{ $item->outstandingQty() }}" class="gh-input" style="width:100%;" wire:model="receiving.{{ $item->id }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="gh-field">
        <span class="gh-label">Notes</span>
        <textarea class="gh-input" style="width:100%;" wire:model="notes"></textarea>
    </div>

    <div class="gh-badge gh-badge--info" style="display:block; padding:10px 12px; font-size:12px;">
        Confirming creates a goods receipt. Stock is credited to your inventory automatically and the PO status advances.
    </div>

    <div style="display:flex; justify-content:flex-end; gap:8px;">
        <a class="gh-btn" href="{{ route('marketplace.purchase-orders.index') }}">Cancel</a>
        <button class="gh-btn gh-btn--primary" wire:click="receive" wire:loading.attr="disabled">Confirm receipt</button>
    </div>
</div>
