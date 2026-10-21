<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Catalog Moderation</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Verify supplier-proposed products before they can appear on the public storefront.</p>
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat" style="cursor:pointer;" wire:click="$set('tab', 'pending')">
            <span class="gh-stat__label">Pending</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">{{ $counts['pending'] }}</span>
        </div>
        <div class="gh-card gh-stat" style="cursor:pointer;" wire:click="$set('tab', 'verified')">
            <span class="gh-stat__label">Verified</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">{{ $counts['verified'] }}</span>
        </div>
        <div class="gh-card gh-stat" style="cursor:pointer;" wire:click="$set('tab', 'rejected')">
            <span class="gh-stat__label">Rejected</span>
            <span class="gh-stat__value" style="color:var(--gh-error);">{{ $counts['rejected'] }}</span>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
            <div class="gh-segmented">
                <button type="button" class="{{ $tab === 'pending' ? 'is-active' : '' }}" wire:click="$set('tab', 'pending')">Pending</button>
                <button type="button" class="{{ $tab === 'verified' ? 'is-active' : '' }}" wire:click="$set('tab', 'verified')">Verified</button>
                <button type="button" class="{{ $tab === 'rejected' ? 'is-active' : '' }}" wire:click="$set('tab', 'rejected')">Rejected</button>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, brand, or part number…" class="gh-input" style="flex:1; min-width:220px;">
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand / Part #</th>
                        <th>Category</th>
                        <th>Proposed by</th>
                        <th>Listings</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td style="font-weight:700;">{{ $product->name }}</td>
                            <td class="gh-muted">{{ $product->brand }}{{ $product->part_number ? ' · ' . $product->part_number : '' }}</td>
                            <td class="gh-muted">{{ $product->category?->name ?? '—' }}</td>
                            <td class="gh-muted">{{ $product->proposer?->name ?? 'Platform' }}</td>
                            <td class="gh-muted">{{ $product->listings_count }}</td>
                            <td style="text-align:right; white-space:nowrap;">
                                @if($tab !== 'verified')
                                    <button class="gh-btn gh-btn--sm gh-btn--primary" wire:click="verify({{ $product->id }})">Verify</button>
                                @endif
                                @if($tab !== 'rejected')
                                    <button class="gh-btn gh-btn--sm" style="color:var(--gh-error);" wire:click="reject({{ $product->id }})" wire:confirm="Reject and deactivate this product?">Reject</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">Nothing here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $products->links() }}</div>
        @endif
    </div>
</div>
