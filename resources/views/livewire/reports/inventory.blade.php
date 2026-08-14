<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Inventory Report</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Stock levels, low inventory, and movement summaries</p>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <div class="gh-grid-4" style="grid-template-columns:repeat(6,1fr);">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Items</span>
            <span class="gh-stat__value">{{ number_format($this->overview['total_items']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">In stock</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">{{ number_format($this->overview['in_stock']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Low stock</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">{{ number_format($this->overview['low_stock']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Out of stock</span>
            <span class="gh-stat__value gh-stat__value--neg">{{ number_format($this->overview['out_of_stock']) }}</span>
        </div>
        <div class="gh-card gh-stat" style="grid-column:span 2;">
            <span class="gh-stat__label">Stock value</span>
            <span class="gh-stat__value">UGX {{ number_format($this->overview['stock_value']) }}</span>
        </div>
    </div>

    <div class="gh-grid-2">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Movement By Type</div>
            @if($this->movementByType->count() > 0)
                <div class="gh-stack" style="gap:12px;">
                    @foreach($this->movementByType as $movement)
                        <div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:12px;">
                                <span>{{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}</span>
                                <span>{{ $movement->count }} records</span>
                            </div>
                            <div class="gh-meter"><div class="gh-meter__fill" style="width:{{ min(($movement->count / max($this->overview['movement_count'], 1)) * 100, 100) }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:32px 0;">No inventory movement data for selected filters</p>
            @endif
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Low Stock Items</div>
            @if($this->lowStockItems->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Item</th><th>Category</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Reorder</th></tr></thead>
                        <tbody>
                            @foreach($this->lowStockItems as $item)
                                <tr>
                                    <td>
                                        <div class="gh-cell-stack"><b>{{ $item->name }}</b><span>{{ $item->sku }}</span></div>
                                    </td>
                                    <td>{{ $item->category?->name ?? 'N/A' }}</td>
                                    <td class="is-num">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="is-num">{{ number_format($item->reorder_level, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:32px 0;">No low stock items</p>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Recent Stock Movements</div>
        @if($this->recentMovements->count() > 0)
            <div class="gh-table-scroll">
                <table class="gh-table">
                    <thead><tr><th>Date</th><th>Item</th><th>Type</th><th>Branch</th><th>By</th><th style="text-align:right;">Qty</th></tr></thead>
                    <tbody>
                        @foreach($this->recentMovements as $movement)
                            <tr>
                                <td class="gh-muted">{{ optional($movement->created_at)->format('d M Y') }}</td>
                                <td>{{ $movement->inventoryItem?->name ?? 'N/A' }}</td>
                                <td><span class="gh-badge">{{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}</span></td>
                                <td>{{ $movement->branch?->name ?? 'N/A' }}</td>
                                <td>{{ $movement->performedBy?->name ?? 'System' }}</td>
                                <td class="is-num">{{ number_format($movement->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="gh-muted" style="text-align:center; padding:32px 0;">No stock movements found</p>
        @endif
    </div>
</div>
