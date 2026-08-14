<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Stock Movements</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Track all inventory changes</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="gh-btn gh-btn--sm">← Back to inventory</a>
    </div>

    <div class="gh-table-toolbar">
        <div class="gh-table-toolbar__filters">
            <select wire:model.live="item" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All items</option>
                @foreach($this->items as $i)
                    <option value="{{ $i->id }}">{{ $i->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="type" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All types</option>
                <option value="purchase">Purchase</option>
                <option value="consumption">Consumption</option>
                <option value="adjustment">Adjustment</option>
                <option value="transfer">Transfer</option>
                <option value="return">Return</option>
            </select>
            <input type="date" wire:model.live="dateFrom" class="gh-input" style="padding:6px 10px; font-size:12px;">
            <input type="date" wire:model.live="dateTo" class="gh-input" style="padding:6px 10px; font-size:12px;">
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Date</th><th>Item</th><th>Type</th><th style="text-align:right;">Quantity</th><th>Reference</th><th>By</th><th>Notes</th></tr></thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="gh-muted">{{ $movement->movement_date?->format('d M Y H:i') ?? $movement->created_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>
                                @if($movement->inventoryItem)
                                    <a href="{{ route('inventory.show', $movement->inventoryItem) }}">{{ $movement->inventoryItem->name }}</a>
                                @else
                                    <span class="gh-muted">Deleted item</span>
                                @endif
                            </td>
                            <td><span class="gh-badge">{{ ucfirst($movement->movement_type) }}</span></td>
                            <td class="is-num" style="color:{{ (($movement->quantity_change ?? 0) > 0) ? 'var(--gh-success)' : 'var(--gh-error)' }};">{{ (($movement->quantity_change ?? 0) > 0) ? '+' : '' }}{{ $movement->quantity_change ?? 0 }}</td>
                            <td>
                                @php $ref = $movement->reference(); @endphp
                                @if($ref && $movement->reference_type === 'work_order')
                                    <a href="{{ route('work-orders.show', $movement->reference_id) }}" class="is-ref">{{ $ref->order_number }}</a>
                                @else
                                    <span class="gh-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $movement->performedBy?->name ?? 'System' }}</td>
                            <td class="gh-muted" style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $movement->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No movements found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="gh-pagination">{{ $movements->links() }}</div>
        @endif
    </div>
</div>
