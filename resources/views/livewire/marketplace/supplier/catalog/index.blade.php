<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Catalog</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">The shared product catalog. Every listing points at an entry here.</p>
        </div>
        @if($isVerifiedSupplier)
            <a href="{{ route('supplier.catalog.create') }}" class="gh-btn gh-btn--primary">+ Propose product</a>
        @endif
    </div>

    @unless($isVerifiedSupplier)
        <div class="gh-badge gh-badge--warning" style="display:block; padding:10px 12px; font-size:12px;">
            Browse the catalog while your approval or plan is pending. An approved business and active supplier plan are required to propose products. <a href="{{ route('billing.subscription') }}" class="link">View plans and billing</a>.
        </div>
    @endunless

    <div class="gh-card gh-card--pad">
        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, brand, or part number…" class="gh-input" style="flex:1; min-width:220px;">
            <label style="display:flex; align-items:center; gap:6px; font-size:12.5px;">
                <input type="checkbox" wire:model.live="mineOnly">
                My submissions only
            </label>
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
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td style="font-weight:700;">{{ $product->name }}</td>
                            <td class="gh-muted">{{ $product->brand }}{{ $product->part_number ? ' · ' . $product->part_number : '' }}</td>
                            <td class="gh-muted">{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                @if($product->is_verified)
                                    <span class="gh-badge gh-badge--success">Verified</span>
                                @else
                                    <span class="gh-badge gh-badge--warning">Pending review</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $products->links() }}</div>
        @endif
    </div>
</div>
