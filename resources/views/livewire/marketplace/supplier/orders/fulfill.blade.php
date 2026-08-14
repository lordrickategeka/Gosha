<div class="gh-page">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Fulfil — {{ $purchaseOrder->po_number }}</div>
    <div class="gh-badge gh-badge--info" style="display:block; padding:10px 12px; font-size:12px;">
        Supplier fulfilment view is stubbed. Buyer-side goods receipt credits stock.
    </div>
    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Item</th><th>Ordered</th><th>Received</th></tr></thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $item)
                        <tr>
                            <td style="font-weight:700;">{{ $item->product?->name ?? $item->description }}</td>
                            <td class="gh-muted">{{ $item->qty_ordered }}</td>
                            <td class="gh-muted">{{ $item->qty_received }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
