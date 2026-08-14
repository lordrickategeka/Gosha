<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('suppliers.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:20px; font-weight:700;">{{ $supplier->name }}</span>
                <span class="gh-badge {{ $supplier->is_active ? 'gh-badge--success' : 'gh-badge--error' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Items from this Supplier</div>
            @if($supplier->inventoryItems->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Item</th><th>SKU</th><th>Stock</th><th>Cost</th></tr></thead>
                        <tbody>
                            @foreach($supplier->inventoryItems as $item)
                                <tr>
                                    <td><a href="{{ route('inventory.show', $item) }}" class="is-ref">{{ $item->name }}</a></td>
                                    <td class="gh-muted">{{ $item->sku ?? '—' }}</td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>UGX {{ number_format($item->cost_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:32px 0;">No items from this supplier</p>
            @endif
        </div>

        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Contact Info</div>
                <div class="gh-stack" style="gap:10px; font-size:12.5px;">
                    @if($supplier->contact_person)<div>{{ $supplier->contact_person }}</div>@endif
                    <div>{{ $supplier->phone }}</div>
                    @if($supplier->email)<div>{{ $supplier->email }}</div>@endif
                    @if($supplier->address)<div>{{ $supplier->address }}</div>@endif
                </div>
            </div>

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Summary</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Total items</span><b>{{ $supplier->inventoryItems->count() }}</b></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Added</span><span>{{ $supplier->created_at->format('d M Y') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
