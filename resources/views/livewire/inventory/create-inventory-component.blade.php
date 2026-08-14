<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('inventory.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Add Inventory Item</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">Create a new part or supply</p>
        </div>
    </div>

    <form wire:submit="save" style="max-width:48rem;">
        <div class="gh-card gh-card--pad">
            <div class="gh-grid-2">
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Item name *</span>
                    <input type="text" wire:model="name" class="gh-input" style="width:100%;">
                    @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">SKU</span>
                    <input type="text" value="{{ $sku ?: 'Select a category to generate SKU' }}" class="gh-input" style="width:100%; background:var(--gh-base-200);" readonly>
                    <span class="gh-hint">Generated automatically from the selected category.</span>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Category *</span>
                    <select wire:model="category_id" class="gh-select" style="width:100%;">
                        <option value="">Select category…</option>
                        @foreach($this->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Supplier</span>
                    <select wire:model="supplier_id" class="gh-select" style="width:100%;">
                        <option value="">Select supplier…</option>
                        @foreach($this->suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Unit</span>
                    <select wire:model="unit" class="gh-select" style="width:100%;">
                        <option value="pcs">Pieces</option>
                        <option value="liters">Liters</option>
                        <option value="kg">Kilograms</option>
                        <option value="meters">Meters</option>
                        <option value="sets">Sets</option>
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Initial quantity</span>
                    <input type="number" wire:model="quantity" class="gh-input" style="width:100%;" min="0">
                    @error('quantity') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Reorder level *</span>
                    <input type="number" wire:model="reorder_level" class="gh-input" style="width:100%;" min="0">
                    @error('reorder_level') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Cost price (UGX) *</span>
                    <input type="number" wire:model="cost_price" class="gh-input" style="width:100%;" min="0">
                    @error('cost_price') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Selling price (UGX) *</span>
                    <input type="number" wire:model="selling_price" class="gh-input" style="width:100%;" min="0">
                    @error('selling_price') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Storage location</span>
                    <input type="text" wire:model="location" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Description</span>
                    <textarea wire:model="description" rows="2" class="gh-input" style="width:100%;"></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:16px; margin-top:20px;">
                <a href="{{ route('inventory.index') }}" class="gh-btn">Cancel</a>
                <button type="submit" class="gh-btn gh-btn--primary">Create item</button>
            </div>
        </div>
    </form>
</div>
