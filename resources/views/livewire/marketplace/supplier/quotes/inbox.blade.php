<div class="gh-page">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">RFQ Inbox</div>

    <div class="gh-stack">
        @forelse ($rfqs as $rfq)
            <div class="gh-card gh-card--pad" style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
                <div>
                    <div style="font-weight:700; font-size:13.5px;">{{ $rfq->title ?: $rfq->reference }}</div>
                    <div class="gh-muted" style="font-size:11.5px; margin-top:2px;">
                        {{ $rfq->buyer?->name ?? 'Buyer' }} &middot; {{ $rfq->items->count() }} item(s)
                        @if ($rfq->closes_at) &middot; closes {{ $rfq->closes_at->diffForHumans() }} @endif
                    </div>
                </div>
                <div>
                    @if ($rfq->has_quoted)
                        <span class="gh-badge gh-badge--success">Quoted</span>
                    @else
                        <a class="gh-btn gh-btn--primary gh-btn--sm" href="{{ route('supplier.quotes.compose', $rfq) }}">Submit quote</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="gh-muted" style="font-size:12.5px;">No open RFQs to quote on right now.</p>
        @endforelse
    </div>
    {{ $rfqs->links() }}
</div>
