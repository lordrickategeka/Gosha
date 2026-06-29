<div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">Submit Quote — {{ $rfq->reference }}</h1>
    <div class="alert alert-info">
        <span>Quote builder is stubbed. RFQ context loaded below; line-item entry &amp; total recalculation are the next step.</span>
    </div>
    <table class="table">
        <thead><tr><th>Item</th><th>Qty</th><th>Target price</th></tr></thead>
        <tbody>
            @foreach ($rfq->items as $item)
                <tr>
                    <td>{{ $item->label() }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ $item->target_price ? number_format($item->target_price) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
