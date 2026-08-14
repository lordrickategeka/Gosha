<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Inventory</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage parts and supplies</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('inventory.low-stock') }}" class="gh-btn gh-btn--sm" style="color:var(--gh-warning);">Low stock ({{ $stats['low_stock'] }})</a>
            @can('create_inventory')
                <button type="button" wire:click="openCreateModal" class="gh-btn gh-btn--primary">+ Add item</button>
            @endcan
        </div>
    </div>

    <div class="gh-grid-4" style="grid-template-columns:repeat(5,1fr);">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total items</span>
            <span class="gh-stat__value">{{ number_format($stats['total']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Service parts</span>
            <span class="gh-stat__value">{{ number_format($stats['service_parts']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Wash supplies</span>
            <span class="gh-stat__value">{{ number_format($stats['wash_supplies']) }}</span>
        </div>
        <div class="gh-card gh-stat" style="background:var(--gh-warning-bg); border-color:var(--gh-warning);">
            <span class="gh-stat__label">Low stock</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">{{ number_format($stats['low_stock']) }}</span>
        </div>
        <div class="gh-card gh-stat" style="background:var(--gh-error-bg); border-color:var(--gh-error);">
            <span class="gh-stat__label">Out of stock</span>
            <span class="gh-stat__value gh-stat__value--neg">{{ number_format($stats['out_of_stock']) }}</span>
        </div>
    </div>

    <div class="gh-table-toolbar">
        <div class="gh-table-toolbar__filters">
            <label class="gh-search" style="width:190px;">⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, SKU, barcode…"></label>
            <select wire:model.live="itemType" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All types</option>
                <option value="service_part">Service Parts</option>
                <option value="wash_supply">Wash Supplies</option>
            </select>
            <select wire:model.live="category" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All categories</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->full_path }}</option>
                @endforeach
            </select>
            <select wire:model.live="stockStatus" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All stock</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
                <option value="in_stock">In Stock</option>
            </select>
            <select wire:model.live="condition" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All conditions</option>
                <option value="new">New</option>
                <option value="used">Used</option>
                <option value="refurbished">Refurbished</option>
            </select>
            @if($search || $itemType || $category || $stockStatus || $condition)
                <button wire:click="clearFilters" class="gh-btn gh-btn--sm">Clear</button>
            @endif
        </div>
    </div>

    @if($items->count())
        <div class="gh-stack">
            @foreach($groupedItems as $categoryName => $categoryItems)
                <div class="gh-card gh-card--flush">
                    <div class="collapse collapse-arrow">
                        <input type="checkbox" checked="checked">
                        <div class="collapse-title" style="background:var(--gh-surface-header); font-weight:700; display:flex; align-items:center; justify-content:space-between; padding:14px 18px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span class="gh-badge">{{ $categoryItems->count() }}</span>
                                <span style="font-size:14.5px;">{{ $categoryName }}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:14px; font-size:12px; font-weight:400;">
                                <span class="gh-muted">Total value: <b style="color:var(--gh-ink); font-weight:700;">UGX {{ number_format($categoryItems->sum(fn($i) => $i->quantity * $i->cost_price)) }}</b></span>
                                <span class="gh-badge gh-badge--warning">{{ $categoryItems->where('quantity', '<=', 'reorder_level')->count() }} Low</span>
                                <span class="gh-badge gh-badge--error">{{ $categoryItems->where('quantity', '<=', 0)->count() }} Out</span>
                            </div>
                        </div>
                        <div class="collapse-content" style="padding:0;">
                            <div class="gh-table-scroll">
                                <table class="gh-table">
                                    <thead>
                                        <tr><th class="is-index">#</th><th>Item details</th><th>SKU / Codes</th><th style="text-align:center;">Stock</th><th style="text-align:right;">Cost price</th><th style="text-align:right;">Selling price</th><th style="text-align:center;">Status</th><th style="text-align:right;">Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categoryItems as $item)
                                            <tr>
                                                <td class="is-index">{{ $loop->iteration }}</td>

                                                <td>
                                                    <div style="display:flex; align-items:flex-start; gap:10px;">
                                                        @if($item->image_path)
                                                            <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}" style="width:44px; height:44px; border-radius:8px; object-fit:cover;">
                                                        @else
                                                            <div class="gh-module-card__icon">{{ strtoupper(substr($item->name, 0, 1)) }}</div>
                                                        @endif
                                                        <div style="min-width:0;">
                                                            <a href="{{ route('inventory.show', $item) }}" class="is-ref" style="font-weight:700;">{{ $item->name }}</a>
                                                            @if($item->brand)<span class="gh-badge" style="margin-left:6px;">{{ $item->brand }}</span>@endif
                                                            @if($item->description)<p class="gh-muted" style="font-size:11px; margin-top:4px; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $item->description }}</p>@endif
                                                            <div style="display:flex; gap:6px; margin-top:4px;">
                                                                @if($item->position)<span class="gh-badge">{{ ucfirst($item->position) }}</span>@endif
                                                                @if($item->condition)<span class="gh-badge {{ $item->condition === 'new' ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ ucfirst($item->condition) }}</span>@endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="gh-stack" style="gap:4px;">
                                                        @if($item->sku)<div class="gh-muted" style="font-size:11px;">SKU: <code style="background:var(--gh-base-200); padding:1px 6px; border-radius:4px;">{{ $item->sku }}</code></div>@endif
                                                        @if($item->oem_number)<div class="gh-muted" style="font-size:11px;">OEM: <code style="background:var(--gh-base-200); padding:1px 6px; border-radius:4px;">{{ $item->oem_number }}</code></div>@endif
                                                        @if($item->barcode)<div class="gh-muted" style="font-size:11px;">Barcode: <code style="background:var(--gh-base-200); padding:1px 6px; border-radius:4px;">{{ $item->barcode }}</code></div>@endif
                                                    </div>
                                                </td>

                                                <td style="text-align:center;">
                                                    <div style="font-weight:800; font-size:16px;">{{ number_format($item->quantity, 2) }}</div>
                                                    <div class="gh-muted" style="font-size:11px;">{{ $item->unit }}</div>
                                                    @if($item->quantity <= $item->reorder_level && $item->quantity > 0)
                                                        <div style="font-size:10.5px; color:var(--gh-warning); margin-top:2px;">Reorder at {{ $item->reorder_level }}</div>
                                                    @endif
                                                </td>

                                                <td class="is-num">
                                                    <div>UGX {{ number_format($item->cost_price) }}</div>
                                                    <div class="gh-muted" style="font-size:10.5px; font-weight:400;">Total: {{ number_format($item->quantity * $item->cost_price) }}</div>
                                                </td>

                                                <td class="is-num">
                                                    <div>UGX {{ number_format($item->selling_price) }}</div>
                                                    @if($item->selling_price > 0)
                                                        @php $margin = (($item->selling_price - $item->cost_price) / $item->selling_price) * 100; @endphp
                                                        <div style="font-size:10.5px; font-weight:400; color:{{ $margin > 0 ? 'var(--gh-success)' : 'var(--gh-error)' }};">{{ number_format($margin, 1) }}% margin</div>
                                                    @endif
                                                </td>

                                                <td style="text-align:center;">
                                                    @if($item->quantity <= 0)
                                                        <span class="gh-badge gh-badge--error">Out of stock</span>
                                                    @elseif($item->quantity <= $item->reorder_level)
                                                        <span class="gh-badge gh-badge--warning">Low stock</span>
                                                    @else
                                                        <span class="gh-badge gh-badge--success">In stock</span>
                                                    @endif
                                                    @if($item->expiry_date)
                                                        @php $daysToExpiry = now()->diffInDays($item->expiry_date, false); @endphp
                                                        @if($daysToExpiry < 0)
                                                            <div class="gh-badge gh-badge--error" style="margin-top:4px;">Expired</div>
                                                        @elseif($daysToExpiry <= 30)
                                                            <div class="gh-badge gh-badge--warning" style="margin-top:4px;">{{ $daysToExpiry }}d to expiry</div>
                                                        @endif
                                                    @endif
                                                </td>

                                                <td onclick="event.stopPropagation()">
                                                    <div class="dropdown dropdown-end">
                                                        <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                                        <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-52 gh-card p-2 shadow-xl">
                                                            <li><a href="{{ route('inventory.show', $item) }}">View details</a></li>
                                                            @can('edit_inventory')
                                                                <li><a href="{{ route('inventory.edit', $item) }}">Edit item</a></li>
                                                            @endcan
                                                            <div class="divider my-0"></div>
                                                            @can('adjust_stock')
                                                                <li><a wire:click="$dispatch('openAdjustModal', { itemId: {{ $item->id }} })">Adjust stock</a></li>
                                                                <li><a href="{{ route('inventory.movements') }}?item={{ $item->id }}">View history</a></li>
                                                            @endcan
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="gh-pagination">{{ $items->links() }}</div>
    @else
        <div class="gh-card gh-card--pad" style="text-align:center; padding:60px 0;">
            <p style="font-size:16px; font-weight:700; margin-top:14px;">No inventory items found</p>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">
                @if($search || $itemType || $category || $stockStatus)
                    Try adjusting your filters or search terms
                @else
                    Get started by adding your first inventory item
                @endif
            </p>
            <div style="margin-top:20px;">
                @if($search || $itemType || $category || $stockStatus)
                    <button wire:click="clearFilters" class="gh-btn gh-btn--primary">Clear filters</button>
                @else
                    @can('create_inventory')
                        <button type="button" wire:click="openCreateModal" class="gh-btn gh-btn--primary">Add first item</button>
                    @endcan
                @endif
            </div>
        </div>
    @endif

    @if($showCreateModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box gh-card gh-card--pad" style="max-width:64rem;">
                <div class="gh-card__title" style="margin-bottom:16px;">Add Inventory Item</div>

                <div class="gh-grid-2">
                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Item name *</span>
                        <input type="text" wire:model="createName" class="gh-input" style="width:100%;">
                        @error('createName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Item type *</span>
                        <select wire:model="createItemType" class="gh-select" style="width:100%;">
                            <option value="service_part">Service Part</option>
                            <option value="wash_supply">Wash Supply</option>
                        </select>
                        @error('createItemType') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Category *</span>
                        <select wire:model="createCategoryId" class="gh-select" style="width:100%;">
                            <option value="">Select category…</option>
                            @foreach($this->createCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('createCategoryId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">SKU</span>
                        <input type="text" value="{{ $createSku ?: 'Select a category to generate SKU' }}" class="gh-input" style="width:100%; background:var(--gh-base-200);" readonly>
                        <span class="gh-hint">Generated automatically from the selected category.</span>
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Barcode</span>
                        <input type="text" wire:model="createBarcode" class="gh-input" style="width:100%;">
                        @error('createBarcode') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Supplier</span>
                        <select wire:model="createSupplierId" class="gh-select" style="width:100%;">
                            <option value="">Select supplier…</option>
                            @foreach($this->suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('createSupplierId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Brand</span>
                        <input type="text" wire:model="createBrand" class="gh-input" style="width:100%;">
                        @error('createBrand') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Unit *</span>
                        <select wire:model="createUnit" class="gh-select" style="width:100%;">
                            <option value="pcs">Pieces</option>
                            <option value="liters">Liters</option>
                            <option value="kg">Kilograms</option>
                            <option value="meters">Meters</option>
                            <option value="sets">Sets</option>
                        </select>
                        @error('createUnit') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Condition *</span>
                        <select wire:model="createCondition" class="gh-select" style="width:100%;">
                            <option value="new">New</option>
                            <option value="used">Used</option>
                            <option value="refurbished">Refurbished</option>
                            <option value="reconditioned">Reconditioned</option>
                        </select>
                        @error('createCondition') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Initial quantity *</span>
                        <input type="number" wire:model="createQuantity" class="gh-input" style="width:100%;" min="0" step="0.01">
                        @error('createQuantity') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Reorder level *</span>
                        <input type="number" wire:model="createReorderLevel" class="gh-input" style="width:100%;" min="0" step="0.01">
                        @error('createReorderLevel') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Cost price (UGX) *</span>
                        <input type="number" wire:model="createCostPrice" class="gh-input" style="width:100%;" min="0" step="0.01">
                        @error('createCostPrice') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Selling price (UGX) *</span>
                        <input type="number" wire:model="createSellingPrice" class="gh-input" style="width:100%;" min="0" step="0.01">
                        @error('createSellingPrice') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Storage location</span>
                        <input type="text" wire:model="createLocation" class="gh-input" style="width:100%;">
                        @error('createLocation') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Description</span>
                        <textarea wire:model="createDescription" rows="3" class="gh-input" style="width:100%;"></textarea>
                        @error('createDescription') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:16px; margin-top:20px;">
                    <button type="button" wire:click="closeCreateModal" class="gh-btn">Cancel</button>
                    <button type="button" wire:click="saveInventoryItem" class="gh-btn gh-btn--primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveInventoryItem">Create item</span>
                        <span wire:loading wire:target="saveInventoryItem">Creating…</span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeCreateModal"></div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    Livewire.on('filtersCleared', () => {});
</script>
@endpush
