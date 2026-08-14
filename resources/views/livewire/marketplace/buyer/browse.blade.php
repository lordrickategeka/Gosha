<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Marketplace</div>
        <a class="gh-btn gh-btn--sm" href="{{ route('marketplace.rfqs.create') }}">Request a quote (RFQ)</a>
    </div>

    @if ($focusProduct)
        <div class="gh-badge gh-badge--info" style="display:block; padding:10px 12px; font-size:12px;">
            Showing suppliers for <strong>{{ $focusProduct->brand }} {{ $focusProduct->name }}</strong>
            ({{ $focusProduct->part_number }}).
            <a href="{{ route('marketplace.browse') }}" wire:navigate style="text-decoration:underline;">Clear filter</a>
        </div>
    @endif

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search part name, brand, or part number" class="gh-input" style="max-width:28rem;">

    <div class="gh-grid-3">
        @forelse ($listings as $listing)
            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                    <div style="font-weight:700; font-size:14px;">{{ $listing->product?->brand }} {{ $listing->product?->name }}</div>
                    @if ($listing->supplier?->is_verified_supplier)
                        <span class="gh-badge gh-badge--success">Verified</span>
                    @endif
                </div>
                <p class="gh-muted" style="font-size:11.5px; font-family:monospace; margin-top:4px;">{{ $listing->product?->part_number }}</p>
                <p style="font-size:12.5px; margin-top:2px;">{{ $listing->supplier?->name }}</p>

                <div style="display:flex; align-items:baseline; gap:6px; margin-top:10px;">
                    <span style="font-size:19px; font-weight:800;">{{ number_format($listing->price) }}</span>
                    <span class="gh-muted" style="font-size:12px;">{{ $listing->currency }}</span>
                </div>
                @if ($listing->priceTiers->isNotEmpty())
                    <p style="font-size:11px; color:var(--gh-success); margin-top:4px;">
                        Bulk: {{ number_format($listing->priceTiers->last()->unit_price) }} @ {{ $listing->priceTiers->last()->min_qty }}+
                    </p>
                @endif
                <p class="gh-muted" style="font-size:11px; margin-top:4px;">
                    Stock {{ $listing->stock_qty }} &middot; MOQ {{ $listing->min_order_qty }} &middot; lead {{ $listing->lead_time_days }}d
                </p>

                <div style="display:flex; justify-content:flex-end; margin-top:12px;">
                    <button class="gh-btn gh-btn--primary gh-btn--sm" wire:click="buy({{ $listing->id }}, {{ $listing->min_order_qty }})" wire:loading.attr="disabled">
                        Buy ({{ $listing->min_order_qty }})
                    </button>
                </div>
            </div>
        @empty
            <p class="gh-muted" style="grid-column:1/-1; text-align:center; padding:40px 0;">No listings match.</p>
        @endforelse
    </div>
    {{ $listings->links() }}
</div>
