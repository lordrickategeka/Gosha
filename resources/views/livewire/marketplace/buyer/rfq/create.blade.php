<div class="gh-page" style="max-width:52rem;">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">New RFQ</div>

    <div class="gh-card gh-card--pad">
        <div class="gh-grid-2">
            <div class="gh-field">
                <span class="gh-label">Title</span>
                <input class="gh-input" style="width:100%;" wire:model="title" placeholder="e.g. Brake parts for fleet">
            </div>
            <div class="gh-field">
                <span class="gh-label">Visibility</span>
                <select class="gh-select" style="width:100%;" wire:model="visibility">
                    <option value="open">Open (any supplier)</option>
                    <option value="targeted">Targeted (invited suppliers)</option>
                </select>
            </div>
            <div class="gh-field" style="grid-column:1/-1;">
                <span class="gh-label">Notes</span>
                <textarea class="gh-input" style="width:100%;" wire:model="notes"></textarea>
            </div>
            <div class="gh-field">
                <span class="gh-label">Closes at</span>
                <input type="datetime-local" class="gh-input" style="width:100%;" wire:model="closes_at">
                @error('closes_at') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <div>
        <p class="gh-eyebrow" style="margin-bottom:10px;">Items</p>
        @error('items') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror

        <div class="gh-stack">
            @foreach ($items as $i => $row)
                <div class="gh-card gh-card--pad" style="display:grid; grid-template-columns:2.5fr 1.5fr 0.8fr 1.2fr auto; gap:10px; align-items:end;">
                    <div class="gh-field">
                        <span class="gh-label">Catalog product</span>
                        <select class="gh-select" style="width:100%;" wire:model="items.{{ $i }}.catalog_product_id">
                            <option value="">— or describe below —</option>
                            @foreach ($this->products as $p)
                                <option value="{{ $p->id }}">{{ $p->brand }} {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Free-text</span>
                        <input class="gh-input" style="width:100%;" wire:model="items.{{ $i }}.description">
                        @error("items.$i.description") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Qty</span>
                        <input type="number" class="gh-input" style="width:100%;" wire:model="items.{{ $i }}.qty">
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Target price</span>
                        <input type="number" step="0.01" class="gh-input" style="width:100%;" wire:model="items.{{ $i }}.target_price">
                    </div>
                    <button class="gh-btn gh-btn--sm" style="color:var(--gh-error);" wire:click="removeItem({{ $i }})">✕</button>
                </div>
            @endforeach
        </div>

        <button class="gh-btn gh-btn--sm" style="margin-top:10px;" wire:click="addItem">+ Add item</button>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:8px;">
        <a class="gh-btn" href="{{ route('marketplace.browse') }}">Cancel</a>
        <button class="gh-btn gh-btn--primary" wire:click="save">Publish RFQ</button>
    </div>
</div>
