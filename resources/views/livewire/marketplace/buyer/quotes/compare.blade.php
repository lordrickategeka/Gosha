<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Compare Quotes — {{ $rfq->reference }}</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">{{ $rfq->title }}</p>
    </div>

    @if ($quotes->isEmpty())
        <p class="gh-muted" style="font-size:12.5px;">No submitted quotes yet.</p>
    @else
        <div class="gh-grid-3">
            @foreach ($quotes as $index => $quote)
                <div class="gh-card gh-card--pad" style="{{ $index === 0 ? 'border-color:var(--gh-success); border-width:2px;' : '' }}">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                        <div style="font-weight:700; font-size:14px;">{{ $quote->supplier?->name }}</div>
                        @if ($index === 0)<span class="gh-badge gh-badge--success">Lowest</span>@endif
                    </div>
                    <div style="font-size:20px; font-weight:800; margin-top:8px;">{{ number_format($quote->total) }} {{ $quote->currency }}</div>
                    @if ($quote->valid_until)
                        <p class="gh-muted" style="font-size:11px; margin-top:2px;">Valid until {{ $quote->valid_until->toFormattedDateString() }}</p>
                    @endif
                    <ul class="gh-stack" style="gap:4px; margin-top:10px; font-size:12px;">
                        @foreach ($quote->items as $qi)
                            <li style="display:flex; justify-content:space-between;">
                                <span>{{ $qi->description ?? $qi->catalog_product_id }} &times; {{ $qi->qty }}</span>
                                <span style="font-weight:600;">{{ number_format($qi->line_total) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div style="display:flex; justify-content:flex-end; margin-top:12px;">
                        <button class="gh-btn gh-btn--primary gh-btn--sm" wire:click="award({{ $quote->id }})" wire:confirm="Award this quote? A draft PO will be created and other quotes rejected." wire:loading.attr="disabled">
                            Award
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
