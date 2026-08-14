<div class="gh-page">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">{{ $listing->product?->brand }} {{ $listing->product?->name }}</div>
    <div class="gh-badge gh-badge--info" style="display:block; padding:10px 12px; font-size:12px;">
        Full detail view is stubbed (compatibility table, tier breakdown, supplier profile).
    </div>
    <div class="gh-card gh-card--pad">
        <p style="font-size:13px;">Price: {{ number_format($listing->price) }} {{ $listing->currency }}</p>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Supplier: {{ $listing->supplier?->name }}</p>
    </div>
</div>
