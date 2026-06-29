<div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">Fulfil — {{ $purchaseOrder->po_number }}</h1>
    <div class="alert alert-info"><span>Supplier fulfilment view is stubbed. Buyer-side goods receipt credits stock.</span></div>
    <table class="table">
        <thead><tr><th>Item</th><th>Ordered</th><th>Received</th></tr></thead>
        <tbody>
            @foreach ($purchaseOrder->items as $item)
                <tr><td>{{ $item->product?->name ?? $item->description }}</td><td>{{ $item->qty_ordered }}</td><td>{{ $item->qty_received }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
