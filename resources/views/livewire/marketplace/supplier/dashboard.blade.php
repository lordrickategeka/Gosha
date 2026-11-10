<div class="gh-page">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Supplier Dashboard</div>
    <section class="gh-card gh-card--pad">
        <h2 class="gh-card__title">{{ $subscription?->plan?->name ?? 'Choose a supplier plan' }}</h2>
        <p class="gh-muted">@if(!$isVerifiedSupplier)Your trial and paid billing wait for platform approval.@elseif($subscription?->isTrialing())Your free trial ends {{ $subscription->trial_ends_at->format('d M Y H:i T') }}. No card or automatic charge.@elseif(!$canSell)New selling is paused. Your existing orders and data remain accessible.@elseYour supplier selling access is active.@endif</p>
        <div style="display:flex; gap:16px; margin-top:12px;"><a href="{{ route('billing.subscription') }}" class="link link-primary">Plans and billing</a>@role('supplier_admin')<a href="{{ route('supplier.team') }}" class="link link-primary">Manage team</a>@endrole</div>
    </section>

    @unless($isVerifiedSupplier)
        <div class="gh-badge gh-badge--warning" style="display:block; padding:10px 12px; font-size:12px;">
            Your supplier business is awaiting platform approval. Email verification confirms your login address; a platform administrator must separately approve your business before you can create listings or propose products. You can browse the catalog while the review is pending.
            <div style="margin-top:10px;"><a href="{{ route('supplier.catalog.index') }}" class="link">Browse the parts catalog</a></div>
        </div>
    @endunless

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
