<div class="gh-page" style="max-width:44rem;">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('supplier.catalog.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Propose a catalog product</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Submitted products are reviewed by a platform admin before they can appear on the public store.</p>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-grid-2">
            <div class="gh-field">
                <span class="gh-label">Brand</span>
                <input type="text" wire:model="brand" class="gh-input" style="width:100%;" placeholder="e.g. Bosch">
                @error('brand') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
            <div class="gh-field">
                <span class="gh-label">Part number</span>
                <input type="text" wire:model="part_number" class="gh-input" style="width:100%;" placeholder="e.g. 0451103316">
                @error('part_number') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
            <div class="gh-field" style="grid-column:1/-1;">
                <span class="gh-label">Name *</span>
                <input type="text" wire:model="name" class="gh-input" style="width:100%;" placeholder="e.g. Oil Filter">
                @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
            <div class="gh-field">
                <span class="gh-label">Category</span>
                <select wire:model="category_id" class="gh-select" style="width:100%;">
                    <option value="">— None —</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gh-field">
                <span class="gh-label">Unit of measure *</span>
                <select wire:model="unit_of_measure" class="gh-select" style="width:100%;">
                    <option value="unit">Unit</option>
                    <option value="litre">Litre</option>
                    <option value="set">Set</option>
                    <option value="pair">Pair</option>
                    <option value="metre">Metre</option>
                </select>
            </div>
            <div class="gh-field" style="grid-column:1/-1;">
                <span class="gh-label">Description</span>
                <textarea wire:model="description" rows="3" class="gh-input" style="width:100%;"></textarea>
                @error('description') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:8px;">
        <a class="gh-btn" href="{{ route('supplier.catalog.index') }}">Cancel</a>
        <button class="gh-btn gh-btn--primary" wire:click="save">Submit for review</button>
    </div>
</div>
