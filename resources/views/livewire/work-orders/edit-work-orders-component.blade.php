<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('work-orders.show', $workOrder) }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">{{ $workOrder->order_number }}</span>
                <span class="gh-badge">{{ ucwords(str_replace('_', ' ', $workOrder->status)) }}</span>
            </div>
            <p class="gh-muted" style="font-size:12px; margin-top:2px;">Edit work order</p>
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <!-- Vehicle & Customer (read-only) -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Vehicle &amp; Customer</div>
                <div class="gh-grid-2">
                    <div>
                        <p class="gh-eyebrow" style="margin-bottom:4px;">Vehicle</p>
                        <p style="font-weight:700; font-size:16px;">{{ $workOrder->vehicle->registration_number }}</p>
                        <p class="gh-muted" style="font-size:12px;">{{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}</p>
                    </div>
                    <div>
                        <p class="gh-eyebrow" style="margin-bottom:4px;">Customer</p>
                        <p style="font-weight:700; font-size:13px;">{{ $workOrder->customer->name }}</p>
                        <p class="gh-muted" style="font-size:12px;">{{ $workOrder->customer->phone }}</p>
                    </div>
                </div>
                <p class="gh-hint" style="margin-top:8px;">Customer and vehicle cannot be changed after creation.</p>
            </div>

            <!-- Service Details -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Service Details</div>
                <div class="gh-grid-2">
                    <div class="gh-field">
                        <span class="gh-label">Service type *</span>
                        <select wire:model="type" class="gh-select" style="width:100%;">
                            <option value="service">Service</option>
                            <option value="repair">Repair</option>
                            <option value="diagnostics">Diagnostics</option>
                            <option value="bodywork">Bodywork</option>
                            <option value="electrical">Electrical</option>
                            <option value="ac">A/C</option>
                            <option value="tyres">Tyres</option>
                            <option value="other">Other</option>
                        </select>
                        @error('type') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Priority *</span>
                        <select wire:model="priority" class="gh-select" style="width:100%;">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        @error('priority') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Service bay</span><span class="gh-hint">Optional</span></div>
                        <select wire:model="service_bay_id" class="gh-select" style="width:100%;">
                            <option value="">Assign later…</option>
                            @foreach($this->serviceBays as $bay)
                                <option value="{{ $bay->id }}">{{ $bay->name }}</option>
                            @endforeach
                        </select>
                        @error('service_bay_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Assign technician</span><span class="gh-hint">Optional</span></div>
                        <select wire:model="assigned_technician_id" class="gh-select" style="width:100%;">
                            <option value="">Assign later…</option>
                            @foreach($this->technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_technician_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Mileage in</span><span class="gh-hint">km</span></div>
                        <input type="number" wire:model="mileage_in" placeholder="e.g. 45000" min="0" class="gh-input" style="width:100%;">
                        @error('mileage_in') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Mileage out</span><span class="gh-hint">km</span></div>
                        <input type="number" wire:model="mileage_out" placeholder="e.g. 45100" min="0" class="gh-input" style="width:100%;">
                        @error('mileage_out') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field" style="grid-column:1/-1;">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Estimated completion</span><span class="gh-hint">Now or future</span></div>
                        <input type="datetime-local" wire:model="estimated_completion" class="gh-input" style="width:100%;">
                        @error('estimated_completion') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field" style="grid-column:1/-1;">
                        <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                            <input type="checkbox" wire:model="is_combo">
                            <span>
                                <span style="font-weight:600; font-size:13px;">Combo job</span>
                                <p class="gh-muted" style="font-size:11.5px; margin:2px 0 0;">Automatically creates a wash order when the job is marked ready</p>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Items Left In Vehicle -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Items Left In Vehicle</div>
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px;">
                    <div class="gh-field" style="grid-column:span 2;">
                        <span class="gh-label">Item</span>
                        <input type="text" wire:model="left_item_name" class="gh-input" placeholder="e.g. Wheel spanner">
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Quantity</span>
                        <input type="number" wire:model="left_item_quantity" min="0.01" step="0.01" class="gh-input">
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Reference</span>
                        <input type="text" wire:model="left_item_reference" class="gh-input" placeholder="Optional">
                    </div>
                    <div class="gh-field" style="grid-column:span 3;">
                        <span class="gh-label">Description</span>
                        <input type="text" wire:model="left_item_description" class="gh-input" placeholder="Optional notes">
                    </div>
                    <button type="button" wire:click="addVehicleLeftItem" class="gh-btn">Add item</button>
                </div>

                @if(count($vehicle_left_items) > 0)
                    <div class="gh-table-scroll" style="margin-top:14px;">
                        <table class="gh-table">
                            <thead><tr><th>Item</th><th>Qty</th><th>Reference</th><th>Description</th><th></th></tr></thead>
                            <tbody>
                                @foreach($vehicle_left_items as $index => $leftItem)
                                    <tr>
                                        <td>{{ $leftItem['item_name'] }}</td>
                                        <td>{{ $leftItem['quantity'] }}</td>
                                        <td>{{ $leftItem['reference'] ?: '—' }}</td>
                                        <td>{{ $leftItem['description'] ?: '—' }}</td>
                                        <td><button type="button" wire:click="removeVehicleLeftItem({{ $index }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">Remove</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Line Items -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Job Items</div>

                @error('items') <div class="gh-note" style="margin-bottom:14px; background:var(--gh-error-bg); border-color:var(--gh-error);"><span class="gh-note__body" style="color:var(--gh-error);">{{ $message }}</span></div> @enderror

                @forelse($items as $index => $item)
                    <div class="gh-card gh-card--pad" style="position:relative; margin-bottom:12px;">
                        <button type="button" wire:click="removeItem({{ $index }})" class="gh-btn gh-btn--sm" style="position:absolute; top:10px; right:10px; color:var(--gh-error);" title="Remove item">✕</button>

                        <div style="display:grid; grid-template-columns:repeat(12,1fr); gap:12px; align-items:start;">
                            <div class="gh-field" style="grid-column:span 2;">
                                <span class="gh-label">Type</span>
                                <select wire:model="items.{{ $index }}.item_type" class="gh-select" style="width:100%;">
                                    <option value="labor">Labor</option>
                                    <option value="part">Part</option>
                                </select>
                            </div>
                            <div class="gh-field" style="grid-column:span 8;">
                                <span class="gh-label">Description *</span>
                                <input type="text" wire:model="items.{{ $index }}.description" placeholder="Service or part description…" class="gh-input" style="width:100%;">
                                @error("items.$index.description") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                            </div>
                            <div class="gh-field" style="grid-column:span 2;">
                                <span class="gh-label">Qty *</span>
                                <input type="number" wire:model="items.{{ $index }}.quantity" step="0.01" min="0.01" class="gh-input" style="width:100%;">
                                @error("items.$index.quantity") <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:32px 0; color:var(--gh-ink-faint);">
                        <p style="font-weight:600;">No items</p>
                        <p style="font-size:12.5px;">Add at least one labor or part item</p>
                    </div>
                @endforelse

                <div style="display:flex; gap:8px; margin-top:12px;">
                    <button type="button" wire:click="addItem('labor')" class="gh-btn gh-btn--sm">+ Add labor</button>
                    <button type="button" wire:click="addItem('part')" class="gh-btn gh-btn--sm">+ Add part</button>
                </div>
            </div>

            <!-- Notes -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Notes</div>
                <div class="gh-stack" style="gap:14px;">
                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Customer notes</span><span class="gh-hint">Visible to customer</span></div>
                        <textarea wire:model="customer_notes" rows="3" placeholder="Instructions from the customer, special requests…" class="gh-input" style="width:100%;"></textarea>
                        @error('customer_notes') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Technician notes</span><span class="gh-hint">Internal only</span></div>
                        <textarea wire:model="technician_notes" rows="3" placeholder="Findings, recommendations, internal notes…" class="gh-input" style="width:100%;"></textarea>
                        @error('technician_notes') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div>
            <div class="gh-card gh-card--pad" style="position:sticky; top:14px;">
                <div class="gh-card__title" style="margin-bottom:14px;">Summary</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Items</span><span>{{ count($items) }}</span></div>
                    <div style="display:flex; justify-content:space-between; border-top:1px solid var(--gh-hairline); padding-top:9px;"><span class="gh-muted">Pricing</span><span class="gh-badge gh-badge--warning">Set at quotation</span></div>
                </div>

                <div class="gh-stack" style="gap:8px;">
                    <button wire:click="save" wire:loading.attr="disabled" class="gh-btn gh-btn--primary gh-btn--block">
                        <span wire:loading.remove wire:target="save">Save changes</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    <a href="{{ route('work-orders.show', $workOrder) }}" class="gh-btn gh-btn--block">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
