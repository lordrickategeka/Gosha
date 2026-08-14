<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Low Stock Alert</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Items that need restocking</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="gh-btn gh-btn--sm">← Back to inventory</a>
    </div>

    <div class="gh-grid-3">
        <div class="gh-card gh-stat" style="background:var(--gh-error-bg); border-color:var(--gh-error);">
            <span class="gh-stat__label">Out of stock</span>
            <span class="gh-stat__value gh-stat__value--neg">{{ $stats['out_of_stock'] }}</span>
        </div>
        <div class="gh-card gh-stat" style="background:var(--gh-warning-bg); border-color:var(--gh-warning);">
            <span class="gh-stat__label">Low stock</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">{{ $stats['low_stock'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Restock value</span>
            <span class="gh-stat__value">UGX {{ number_format($stats['total_value_at_risk']) }}</span>
        </div>
    </div>

    <label class="gh-search" style="width:320px;">⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search items…"></label>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Item</th><th>SKU</th><th>Category</th><th>Supplier</th><th style="text-align:right;">Current stock</th><th style="text-align:right;">Reorder level</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($items as $item)
                        <tr style="background:{{ $item->quantity <= 0 ? 'var(--gh-error-bg)' : 'var(--gh-warning-bg)' }};">
                            <td><a href="{{ route('inventory.show', $item) }}" class="is-ref">{{ $item->name }}</a></td>
                            <td class="gh-muted">{{ $item->sku ?? '—' }}</td>
                            <td><span class="gh-badge">{{ $item->category?->name ?? '—' }}</span></td>
                            <td>{{ $item->supplier?->name ?? '—' }}</td>
                            <td class="is-num" style="color:{{ $item->quantity <= 0 ? 'var(--gh-error)' : 'var(--gh-warning)' }};">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="is-num gh-muted">{{ $item->reorder_level }} {{ $item->unit }}</td>
                            <td>
                                @if($item->quantity <= 0)
                                    <span class="gh-badge gh-badge--error">Out of stock</span>
                                @else
                                    <span class="gh-badge gh-badge--warning">Low stock</span>
                                @endif
                            </td>
                            <td><a href="{{ route('inventory.show', $item) }}?adjust=1" class="gh-btn gh-btn--sm gh-btn--primary">Restock</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gh-success);">All items are well stocked!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="gh-pagination">{{ $items->links() }}</div>
        @endif
    </div>
</div>
