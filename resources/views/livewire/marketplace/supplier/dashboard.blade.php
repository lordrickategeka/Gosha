<div class="gh-page">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Supplier Dashboard</div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Active listings</span>
            <span class="gh-stat__value">{{ $activeListings }}</span>
            <a href="{{ route('supplier.listings.index') }}" class="is-ref" style="font-size:11px; margin-top:6px; display:inline-block;">Manage</a>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Open RFQs</span>
            <span class="gh-stat__value" style="color:var(--gh-info);">{{ $openRfqs }}</span>
            <a href="{{ route('supplier.quotes.inbox') }}" class="is-ref" style="font-size:11px; margin-top:6px; display:inline-block;">Quote now</a>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Submitted quotes</span>
            <span class="gh-stat__value">{{ $pendingQuotes }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Incoming orders</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">{{ $incomingOrders }}</span>
            <a href="{{ route('supplier.orders.index') }}" class="is-ref" style="font-size:11px; margin-top:6px; display:inline-block;">View</a>
        </div>
    </div>
</div>
