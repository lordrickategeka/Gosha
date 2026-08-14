<div class="gh-page">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Submit Quote — {{ $rfq->reference }}</div>
    <div class="gh-badge gh-badge--info" style="display:block; padding:10px 12px; font-size:12px;">
        Quote builder is stubbed. RFQ context loaded below; line-item entry &amp; total recalculation are the next step.
    </div>
    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Target price</th></tr></thead>
                <tbody>
                    @foreach ($rfq->items as $item)
                        <tr>
                            <td style="font-weight:700;">{{ $item->label() }}</td>
                            <td class="gh-muted">{{ $item->qty }}</td>
                            <td>{{ $item->target_price ? number_format($item->target_price) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
