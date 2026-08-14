<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('inventory.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div>
                <div style="font-size:20px; font-weight:700;">{{ $item->name }}</div>
                <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">{{ $item->sku ?? 'No SKU' }}</p>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            @can('adjust_stock')
                <button wire:click="$set('showAdjustModal', true)" class="gh-btn gh-btn--primary gh-btn--sm">Adjust stock</button>
            @endcan
            @can('edit_inventory')
                <a href="{{ route('inventory.edit', $item) }}" class="gh-btn gh-btn--sm">Edit</a>
            @endcan
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <!-- Details -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Item Details</div>
                <div class="gh-grid-3">
                    <div><p class="gh-eyebrow" style="margin-bottom:4px;">Category</p><p style="font-weight:600; font-size:13px;">{{ $item->category?->name ?? '—' }}</p></div>
                    <div><p class="gh-eyebrow" style="margin-bottom:4px;">Supplier</p><p style="font-weight:600; font-size:13px;">{{ $item->supplier?->name ?? '—' }}</p></div>
                    <div><p class="gh-eyebrow" style="margin-bottom:4px;">Unit</p><p style="font-weight:600; font-size:13px;">{{ $item->unit }}</p></div>
                    <div><p class="gh-eyebrow" style="margin-bottom:4px;">Cost price</p><p style="font-weight:600; font-size:13px;">UGX {{ number_format($item->cost_price) }}</p></div>
                    <div><p class="gh-eyebrow" style="margin-bottom:4px;">Selling price</p><p style="font-weight:600; font-size:13px;">UGX {{ number_format($item->selling_price) }}</p></div>
                    <div><p class="gh-eyebrow" style="margin-bottom:4px;">Location</p><p style="font-weight:600; font-size:13px;">{{ $item->location ?? '—' }}</p></div>
                </div>
                @if($item->description)
                    <div style="margin-top:14px; border-top:1px solid var(--gh-hairline); padding-top:12px;">
                        <p class="gh-eyebrow" style="margin-bottom:4px;">Description</p>
                        <p style="font-size:13px;">{{ $item->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Movement History -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Recent Movements</div>
                @if($item->movements->count() > 0)
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Date</th><th>Type</th><th>Qty</th><th>By</th><th>Notes</th></tr></thead>
                            <tbody>
                                @foreach($item->movements as $movement)
                                    <tr>
                                        <td class="gh-muted">{{ ($movement->movement_date ?? $movement->created_at)?->format('d M Y, H:i') ?? '—' }}</td>
                                        <td><span class="gh-badge">{{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}</span></td>
                                        <td style="font-weight:700; color:{{ $movement->quantity_change > 0 ? 'var(--gh-success)' : 'var(--gh-error)' }};">{{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}</td>
                                        <td>{{ $movement->performedBy?->name ?? 'System' }}</td>
                                        <td class="gh-muted">{{ $movement->notes ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="gh-muted" style="text-align:center; padding:16px 0;">No movements recorded</p>
                @endif
            </div>
        </div>

        <div class="gh-stack">
            <!-- Stock Status -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Stock Status</div>
                <div style="text-align:center; padding:14px 0;">
                    <p style="font-size:36px; font-weight:800; color:{{ $item->quantity <= 0 ? 'var(--gh-error)' : ($item->quantity <= $item->reorder_level ? 'var(--gh-warning)' : 'var(--gh-success)') }};">{{ $item->quantity }}</p>
                    <p class="gh-muted" style="font-size:12.5px;">{{ $item->unit }} in stock</p>
                    @if($item->quantity <= 0)
                        <span class="gh-badge gh-badge--error" style="margin-top:8px;">Out of stock</span>
                    @elseif($item->quantity <= $item->reorder_level)
                        <span class="gh-badge gh-badge--warning" style="margin-top:8px;">Low stock</span>
                    @else
                        <span class="gh-badge gh-badge--success" style="margin-top:8px;">In stock</span>
                    @endif
                </div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px; border-top:1px solid var(--gh-hairline); padding-top:14px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Reorder level</span><span>{{ $item->reorder_level }} {{ $item->unit }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Stock value</span><b>UGX {{ number_format($item->quantity * $item->cost_price) }}</b></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    @if($showAdjustModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:16px;">Adjust Stock</div>
                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Adjustment type</span>
                    <select wire:model="adjustType" class="gh-select" style="width:100%;">
                        <option value="purchase">Purchase / Restock</option>
                        <option value="consumption">Consumption / Used</option>
                        <option value="adjustment">Manual Adjustment</option>
                        <option value="return">Customer Return</option>
                        <option value="transfer">Transfer Out</option>
                    </select>
                </div>
                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Quantity</span>
                    <input type="number" wire:model="adjustQuantity" class="gh-input" style="width:100%;" min="1">
                    <span class="gh-hint">Current stock: {{ $item->quantity }} {{ $item->unit }}</span>
                </div>
                <div class="gh-field" style="margin-bottom:18px;">
                    <span class="gh-label">Notes</span>
                    <textarea wire:model="adjustNotes" class="gh-input" style="width:100%;" rows="2" placeholder="Reason for adjustment…"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px;">
                    <button wire:click="$set('showAdjustModal', false)" class="gh-btn">Cancel</button>
                    <button wire:click="adjustStock" class="gh-btn gh-btn--primary">Save adjustment</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showAdjustModal', false)"></div>
        </div>
    @endif
</div>
