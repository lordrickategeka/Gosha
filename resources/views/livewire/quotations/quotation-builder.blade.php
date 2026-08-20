<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('work-orders.show', $workOrder) }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">{{ $quotation ? 'Edit Quotation '.$quotation->quotation_number : 'New Quotation' }}</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">Work Order: {{ $workOrder->order_number }} — {{ $workOrder->customer->name }} — {{ $workOrder->vehicle->registration_number }}</p>
        </div>
    </div>

    <div class="gh-split">
        <!-- Line Items -->
        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div class="gh-card__title">Line Items</div>
                    <div style="display:flex; gap:8px;">
                        <button wire:click="addItem('labor')" class="gh-btn gh-btn--sm">+ Labor</button>
                        <button wire:click="addItem('part')" class="gh-btn gh-btn--sm">+ Part</button>
                    </div>
                </div>

                @error('items') <div class="gh-hint" style="color:var(--gh-error); margin-bottom:10px;">{{ $message }}</div> @enderror

                <div class="gh-stack" style="gap:12px;">
                    @forelse($items as $index => $item)
                        <div class="gh-card gh-card--pad">
                            <div class="gh-form-grid" style="display:grid; grid-template-columns:repeat(12,1fr); gap:10px;">
                                <div class="gh-field" style="grid-column:span 2;">
                                    <span class="gh-label">Type</span>
                                    <select wire:model="items.{{ $index }}.item_type" class="gh-select" style="width:100%;">
                                        <option value="labor">Labor</option>
                                        <option value="part">Part</option>
                                    </select>
                                </div>

                                <div class="gh-field" style="grid-column:span 4; position:relative;">
                                    <span class="gh-label">Description</span>
                                    <input type="text" wire:model.live.debounce.300ms="items.{{ $index }}.description" placeholder="Search inventory or type description…" class="gh-input" style="width:100%;">
                                    @error("items.{$index}.description") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror

                                    @if(!empty($item['inventory_item_id']))
                                        <div style="display:flex; align-items:center; gap:6px; margin-top:4px;">
                                            <span class="gh-badge gh-badge--success">Linked to inventory</span>
                                            <button wire:click="clearInventoryLink({{ $index }})" style="font-size:11px; color:var(--gh-error); background:none; border:0; cursor:pointer; text-decoration:underline;">unlink</button>
                                        </div>
                                    @endif

                                    @if(!empty($itemSuggestions[$index]['my_branch']) || !empty($itemSuggestions[$index]['other_branches']))
                                        <div class="gh-card" style="position:absolute; z-index:50; left:0; right:0; top:100%; margin-top:4px; max-height:18rem; overflow-y:auto;">
                                            @if(!empty($itemSuggestions[$index]['my_branch']))
                                                <div style="padding:6px 12px; font-size:11px; font-weight:700; color:var(--gh-ink-subtle); background:var(--gh-base-200);">This branch</div>
                                                @foreach($itemSuggestions[$index]['my_branch'] as $suggestion)
                                                    <button wire:click="selectInventoryItem({{ $index }}, {{ $suggestion['id'] }})" style="width:100%; text-align:left; padding:8px 12px; border:0; background:transparent; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:8px; border-bottom:1px solid var(--gh-hairline);">
                                                        <span style="font-weight:600; font-size:12.5px;">{{ $suggestion['name'] }} @if($suggestion['sku'])<span class="gh-muted" style="font-weight:400;"> ({{ $suggestion['sku'] }})</span>@endif</span>
                                                        <span style="text-align:right; flex-shrink:0;">
                                                            <div class="gh-muted" style="font-size:10.5px;">Qty: {{ $suggestion['quantity'] }}</div>
                                                            <div style="font-size:11px; font-weight:600;">UGX {{ number_format($suggestion['price']) }}</div>
                                                        </span>
                                                    </button>
                                                @endforeach
                                            @endif
                                            @if(!empty($itemSuggestions[$index]['other_branches']))
                                                <div style="padding:6px 12px; font-size:11px; font-weight:700; color:var(--gh-ink-subtle); background:var(--gh-base-200);">Other branches</div>
                                                @foreach($itemSuggestions[$index]['other_branches'] as $suggestion)
                                                    <button wire:click="selectInventoryItem({{ $index }}, {{ $suggestion['id'] }})" style="width:100%; text-align:left; padding:8px 12px; border:0; background:transparent; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:8px; opacity:.75; border-bottom:1px solid var(--gh-hairline);">
                                                        <span style="font-size:12.5px;">{{ $suggestion['name'] }} <span class="gh-badge gh-badge--warning" style="margin-left:4px;">{{ $suggestion['branch_name'] }}</span></span>
                                                        <span class="gh-muted" style="font-size:10.5px; flex-shrink:0;">Qty: {{ $suggestion['quantity'] }}</span>
                                                    </button>
                                                @endforeach
                                            @endif
                                            @if($item['item_type'] === 'part' && empty($item['inventory_item_id']) && strlen($item['description']) >= 2)
                                                <div style="border-top:1px solid var(--gh-hairline); padding:8px 12px;">
                                                    <button wire:click="promptAddToInventory({{ $index }})" style="font-size:12px; color:var(--gh-primary); background:none; border:0; cursor:pointer; text-decoration:underline;">+ Add "{{ $item['description'] }}" to inventory</button>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($item['item_type'] === 'part' && empty($item['inventory_item_id']) && strlen($item['description'] ?? '') >= 2)
                                        <div style="margin-top:4px;">
                                            <button wire:click="promptAddToInventory({{ $index }})" style="font-size:11px; color:var(--gh-primary); background:none; border:0; cursor:pointer; text-decoration:underline;">+ Add to inventory</button>
                                        </div>
                                    @endif
                                </div>

                                <div class="gh-field" style="grid-column:span 2;">
                                    <span class="gh-label">Supplier</span>
                                    <select wire:model="items.{{ $index }}.supplier_id" class="gh-select" style="width:100%;">
                                        <option value="">— none —</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="gh-field" style="grid-column:span 1;">
                                    <span class="gh-label">Qty</span>
                                    <input type="number" wire:model.live.debounce.300ms="items.{{ $index }}.quantity" min="0.01" step="0.01" class="gh-input" style="width:100%;">
                                    @error("items.{$index}.quantity") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                </div>

                                <div class="gh-field" style="grid-column:span 1;">
                                    <span class="gh-label">Unit price</span>
                                    <input type="number" wire:model.live.debounce.300ms="items.{{ $index }}.unit_price" min="0" step="1" class="gh-input" style="width:100%;">
                                    @error("items.{$index}.unit_price") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                </div>

                                <div class="gh-field" style="grid-column:span 1;">
                                    <span class="gh-label">Discount</span>
                                    <input type="number" wire:model.live.debounce.300ms="items.{{ $index }}.discount" min="0" step="1" class="gh-input" style="width:100%;">
                                </div>

                                <div style="grid-column:span 1; display:flex; flex-direction:column; justify-content:flex-end; gap:6px;">
                                    <label style="display:flex; align-items:center; gap:4px; font-size:11px; cursor:pointer;">
                                        <input type="checkbox" wire:model="items.{{ $index }}.vat_applicable"> VAT
                                    </label>
                                    <button wire:click="removeItem({{ $index }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">✕</button>
                                </div>
                            </div>

                            <div style="text-align:right; margin-top:8px; font-size:12px; color:var(--gh-ink-subtle);">
                                Line total: <b style="color:var(--gh-ink);">UGX {{ number_format(max(0, (((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0))) - ((float) ($item['discount'] ?? 0)))) }}</b>
                                @if(($item['vat_applicable'] ?? false) && $vatRate > 0)
                                    <span style="color:var(--gh-warning); margin-left:6px;">+VAT {{ $vatRate }}%</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:32px 0; color:var(--gh-ink-faint);">No items yet. Click "+ Labor" or "+ Part" to add a line.</div>
                    @endforelse
                </div>

                @if(count($items))
                    <div style="display:flex; gap:8px; margin-top:14px;">
                        <button wire:click="addItem('labor')" class="gh-btn gh-btn--sm">+ Add labor</button>
                        <button wire:click="addItem('part')" class="gh-btn gh-btn--sm">+ Add part</button>
                    </div>
                @endif
            </div>

            <!-- Notes & Terms -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Notes &amp; Terms</div>
                <div class="gh-grid-2">
                    <div class="gh-field">
                        <span class="gh-label">Internal / Customer notes</span>
                        <textarea wire:model="notes" rows="4" class="gh-input" style="width:100%;" placeholder="Any notes for the customer…"></textarea>
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Terms &amp; conditions</span>
                        <textarea wire:model="termsAndConditions" rows="4" class="gh-input" style="width:100%;" placeholder="Payment terms, warranty, etc."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary + Actions -->
        <div class="gh-stack">
            <div class="gh-card gh-card--pad" style="position:sticky; top:14px;">
                <div class="gh-card__title" style="margin-bottom:12px;">Summary</div>

                <div class="gh-stack" style="gap:8px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Subtotal</span><b>UGX {{ number_format($this->subtotal) }}</b></div>
                    @if($vatRate > 0)
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">VAT ({{ $vatRate }}%)</span><b>UGX {{ number_format($this->vatAmount) }}</b></div>
                    @endif
                    <div style="border-top:1px solid var(--gh-hairline); padding-top:8px; display:flex; justify-content:space-between; font-weight:800; font-size:16px;"><span>Total</span><span>UGX {{ number_format($this->total) }}</span></div>
                </div>

                <div class="gh-field" style="margin-top:16px;">
                    <span class="gh-label">VAT rate (%)</span>
                    <input type="number" wire:model.live="vatRate" min="0" max="100" step="0.5" class="gh-input" style="width:100%;" placeholder="0">
                    <span class="gh-hint">Applied to lines with VAT toggled on.</span>
                </div>

                <div class="gh-field" style="margin-top:14px;">
                    <span class="gh-label">Valid until</span>
                    <input type="date" wire:model="validUntil" class="gh-input" style="width:100%;">
                    @error('validUntil') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>

                <div class="gh-stack" style="gap:8px; margin-top:20px;">
                    @can('send_quotations')
                        <button wire:click="saveAndSend" class="gh-btn gh-btn--primary gh-btn--block" wire:loading.attr="disabled" wire:target="saveAndSend">
                            <span wire:loading.remove wire:target="saveAndSend">Save &amp; send to customer</span>
                            <span wire:loading wire:target="saveAndSend">Saving…</span>
                        </button>
                    @endcan
                    @can('create_quotations')
                        <button wire:click="saveDraft" class="gh-btn gh-btn--block" wire:loading.attr="disabled" wire:target="saveDraft">
                            <span wire:loading.remove wire:target="saveDraft">Save as draft</span>
                            <span wire:loading wire:target="saveDraft">Saving…</span>
                        </button>
                    @endcan
                    @if($quotation && $quotation->isSent())
                        @can('edit_quotations')
                            <button wire:click="createRevision" class="gh-btn gh-btn--sm gh-btn--block">Create revision (v{{ $quotation->version + 1 }})</button>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add-to-Inventory Modal -->
    @if($showAddToInventoryModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box gh-card gh-card--pad" style="max-width:420px; position:relative;">
                <button wire:click="closeAddToInventoryModal" class="gh-btn gh-btn--sm" style="position:absolute; right:14px; top:14px;">✕</button>
                <div class="gh-card__title" style="margin-bottom:16px;">Add to Inventory</div>

                <div class="gh-stack" style="gap:12px;">
                    <div class="gh-field">
                        <span class="gh-label">Item name *</span>
                        <input type="text" wire:model="newItemName" class="gh-input" style="width:100%;">
                        @error('newItemName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">SKU</span>
                            <input type="text" wire:model="newItemSku" class="gh-input" style="width:100%;">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Unit *</span>
                            <input type="text" wire:model="newItemUnit" class="gh-input" style="width:100%;" placeholder="pcs, litre, kg…">
                            @error('newItemUnit') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Category</span>
                        <select wire:model="newItemCategoryId" class="gh-select" style="width:100%;">
                            <option value="">— select —</option>
                            @foreach($this->inventoryCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Supplier</span>
                        <select wire:model="newItemSupplierId" class="gh-select" style="width:100%;">
                            <option value="">— none —</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup['id'] }}">{{ $sup['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Cost price</span>
                            <input type="number" wire:model="newItemCostPrice" min="0" step="1" class="gh-input" style="width:100%;">
                            @error('newItemCostPrice') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Selling price</span>
                            <input type="number" wire:model="newItemSellingPrice" min="0" step="1" class="gh-input" style="width:100%;">
                            @error('newItemSellingPrice') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:18px;">
                    <button wire:click="closeAddToInventoryModal" class="gh-btn">Cancel</button>
                    <button wire:click="saveAddToInventoryItem" class="gh-btn gh-btn--primary" wire:loading.attr="disabled" wire:target="saveAddToInventoryItem">
                        <span wire:loading.remove wire:target="saveAddToInventoryItem">Save &amp; link</span>
                        <span wire:loading wire:target="saveAddToInventoryItem">Saving…</span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeAddToInventoryModal"></div>
        </div>
    @endif
</div>
