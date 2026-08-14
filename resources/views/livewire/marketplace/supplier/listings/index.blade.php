<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">My Listings</div>
        <button class="gh-btn gh-btn--primary" wire:click="openCreate">+ New listing</button>
    </div>

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product / part no." class="gh-input" style="max-width:22rem;">

    @if ($showForm)
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">{{ $editingId ? 'Edit' : 'New' }} Listing</div>
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">Catalog product</span>
                    <select class="gh-select" style="width:100%;" wire:model="catalog_product_id">
                        <option value="">— select —</option>
                        @foreach ($this->products as $p)
                            <option value="{{ $p->id }}">{{ $p->brand }} {{ $p->name }} ({{ $p->part_number }})</option>
                        @endforeach
                    </select>
                    @error('catalog_product_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Supplier SKU</span>
                    <input class="gh-input" style="width:100%;" wire:model="supplier_sku">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Price ({{ config('marketplace.default_currency') }})</span>
                    <input type="number" step="0.01" class="gh-input" style="width:100%;" wire:model="price">
                    @error('price') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Stock qty</span>
                    <input type="number" class="gh-input" style="width:100%;" wire:model="stock_qty">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Min order qty</span>
                    <input type="number" class="gh-input" style="width:100%;" wire:model="min_order_qty">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Lead time (days)</span>
                    <input type="number" class="gh-input" style="width:100%;" wire:model="lead_time_days">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Condition</span>
                    <select class="gh-select" style="width:100%;" wire:model="condition">
                        <option value="new">New</option>
                        <option value="used">Used</option>
                        <option value="refurbished">Refurbished</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:16px;">
                        <input type="checkbox" wire:model="is_active">
                        <span style="font-weight:600; font-size:12.5px;">Active</span>
                    </label>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                <button class="gh-btn" wire:click="$set('showForm', false)">Cancel</button>
                <button class="gh-btn gh-btn--primary" wire:click="save">Save</button>
            </div>
        </div>
    @endif

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr><th>Product</th><th>SKU</th><th>Price</th><th>Stock</th><th>MOQ</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($listings as $listing)
                        <tr>
                            <td style="font-weight:700;">{{ $listing->product?->brand }} {{ $listing->product?->name }}</td>
                            <td class="gh-muted" style="font-family:monospace;">{{ $listing->supplier_sku }}</td>
                            <td>{{ number_format($listing->price) }}</td>
                            <td class="gh-muted">{{ $listing->stock_qty }}</td>
                            <td class="gh-muted">{{ $listing->min_order_qty }}</td>
                            <td>
                                <span class="gh-badge {{ $listing->is_active ? 'gh-badge--success' : '' }}">
                                    {{ $listing->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <button class="gh-btn gh-btn--sm" wire:click="edit({{ $listing->id }})">Edit</button>
                                <button class="gh-btn gh-btn--sm" wire:click="toggle({{ $listing->id }})">Toggle</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No listings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $listings->links() }}</div>
    </div>
</div>
