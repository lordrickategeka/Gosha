<div class="gh-card gh-card--pad">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
        <div>
            <div class="gh-card__title">Job Items</div>
            <p class="gh-muted" style="font-size:11px; margin-top:2px;">Capture labor and parts required before quotation and pricing.</p>
        </div>

        @if($this->templates->isNotEmpty())
            <select wire:model="selectedTemplate" wire:change="applyTemplate" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">Apply template…</option>
                @foreach($this->templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    @if(count($items) > 0)
        <div class="gh-stack" style="gap:12px; margin-bottom:16px;">
            @foreach($items as $index => $item)
                <div class="gh-card gh-card--pad" style="position:relative;">
                    <button type="button" wire:click="removeItem({{ $index }})" class="gh-btn gh-btn--sm" style="position:absolute; top:10px; right:10px; color:var(--gh-error);">✕</button>

                    <div class="gh-form-grid" style="display:grid; grid-template-columns:repeat(12,1fr); gap:12px; align-items:start;">
                        <div class="gh-field" style="grid-column: span 2;">
                            <span class="gh-label">Type</span>
                            <select wire:model="items.{{ $index }}.item_type" class="gh-select" style="width:100%;">
                                <option value="labor">Labor</option>
                                <option value="part">Part</option>
                            </select>
                        </div>

                        <div class="gh-field" style="grid-column: span 7;" x-data="{ open: false }" x-on:focusin="open = true" x-on:focusout="setTimeout(() => { open = false }, 200)">
                            <span class="gh-label">Description / search inventory *</span>
                            <div style="position:relative;">
                                <input type="text" wire:model.live.debounce.350ms="items.{{ $index }}.description" placeholder="Type to search inventory or enter a custom item…" class="gh-input" style="width:100%;" autocomplete="off">

                                @php
                                    $mySuggestions    = $itemSuggestions[$index]['my_branch']      ?? [];
                                    $otherSuggestions = $itemSuggestions[$index]['other_branches']  ?? [];
                                    $hasSuggestions   = count($mySuggestions) > 0 || count($otherSuggestions) > 0;
                                @endphp

                                @if($hasSuggestions)
                                    <div x-show="open" class="gh-card" style="position:absolute; z-index:50; top:100%; left:0; right:0; margin-top:4px; max-height:16rem; overflow-y:auto;">
                                        @if(count($mySuggestions) > 0)
                                            <div style="padding:6px 12px; font-size:11px; font-weight:700; color:var(--gh-ink-subtle); background:var(--gh-base-200);">This branch</div>
                                            @foreach($mySuggestions as $suggestion)
                                                <button type="button" @mousedown.prevent wire:click="selectInventoryItem({{ $index }}, {{ $suggestion['id'] }})" style="width:100%; text-align:left; padding:8px 12px; border:0; border-bottom:1px solid var(--gh-hairline); background:transparent; cursor:pointer; display:flex; align-items:center; justify-content:space-between; font-size:12.5px;">
                                                    <span style="font-weight:600;">{{ $suggestion['name'] }}</span>
                                                    <span class="gh-muted" style="font-size:11px;">
                                                        {{ $suggestion['sku'] }}
                                                        @if($suggestion['quantity'] > 0)
                                                            · <span style="color:var(--gh-success); font-weight:600;">{{ $suggestion['quantity'] }} {{ $suggestion['unit'] }}</span>
                                                        @else
                                                            · <span style="color:var(--gh-error); font-weight:600;">Out of stock</span>
                                                        @endif
                                                    </span>
                                                </button>
                                            @endforeach
                                        @endif

                                        @if(count($otherSuggestions) > 0)
                                            <div style="padding:6px 12px; font-size:11px; font-weight:700; color:var(--gh-ink-subtle); background:var(--gh-base-200);">Available at other branches</div>
                                            @foreach($otherSuggestions as $suggestion)
                                                <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; border-bottom:1px solid var(--gh-hairline);">
                                                    <div>
                                                        <span style="font-weight:600; font-size:12.5px;">{{ $suggestion['name'] }}</span>
                                                        <span class="gh-muted" style="font-size:11px; margin-left:4px;">{{ $suggestion['sku'] }}</span>
                                                        <div style="font-size:11px; color:var(--gh-warning); font-weight:600; margin-top:2px;">{{ $suggestion['branch_name'] }} · {{ $suggestion['quantity'] }} {{ $suggestion['unit'] }} in stock</div>
                                                    </div>
                                                    <button type="button" @mousedown.prevent wire:click="requestItemFromBranch({{ $index }}, {{ $suggestion['id'] }}, {{ $suggestion['branch_id'] }})" class="gh-btn gh-btn--sm" style="color:var(--gh-warning); flex-shrink:0; margin-left:8px;">Request transfer</button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @elseif(strlen($item['description'] ?? '') >= 2 && empty($item['inventory_item_id'] ?? null))
                                    <div x-show="open" class="gh-card" style="position:absolute; z-index:50; top:100%; left:0; right:0; margin-top:4px; padding:10px 12px; font-size:12.5px; color:var(--gh-ink-subtle); font-style:italic;">No inventory match — will appear on quotation for pricing</div>
                                @endif
                            </div>

                            @if(!empty($item['inventory_item_id']))
                                <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                    @if(!empty($item['source_branch_id']))
                                        <span class="gh-badge gh-badge--warning">⇄ Transfer requested</span>
                                    @else
                                        <span class="gh-badge gh-badge--success">✓ Linked to inventory</span>
                                    @endif
                                    <button type="button" wire:click="clearInventoryLink({{ $index }})" style="font-size:11px; color:var(--gh-error); background:none; border:0; cursor:pointer; text-decoration:underline;">Unlink</button>
                                </div>
                            @endif

                            @error("items.$index.description") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field" style="grid-column: span 3;">
                            <span class="gh-label">Qty *</span>
                            <input type="number" wire:model="items.{{ $index }}.quantity" step="0.01" min="0.01" class="gh-input" style="width:100%;">
                            @error("items.$index.quantity") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align:center; padding:32px 0; color:var(--gh-ink-faint);">
            <p style="font-weight:600;">No items added yet</p>
            <p style="font-size:12.5px;">Add labor or parts to this work order</p>
        </div>
    @endif

    @error('items') <div class="gh-note" style="margin-bottom:14px; background:var(--gh-error-bg); border-color:var(--gh-error);"><span class="gh-note__body" style="color:var(--gh-error);">{{ $message }}</span></div> @enderror

    <div style="display:flex; flex-wrap:wrap; gap:8px;">
        <button type="button" wire:click="addItem('labor')" class="gh-btn gh-btn--sm">+ Add labor</button>
        <button type="button" wire:click="addItem('part')" class="gh-btn gh-btn--sm">+ Add part</button>
    </div>

    <div class="gh-note" style="margin-top:14px;"><span class="gh-note__body">Prices will be set during the quotation stage. Just add items and quantities for now.</span></div>
</div>

<div style="border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
    <div style="display:flex; justify-content:space-between; gap:8px;">
        <button type="button" wire:click="previousStep" class="gh-btn">← Back</button>
        <button type="button" wire:click="nextStep" class="gh-btn gh-btn--primary">Continue to review →</button>
    </div>
</div>
