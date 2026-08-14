<div class="gh-card gh-card--pad">
    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:8px; margin-bottom:16px;">
        <div class="gh-card__title">Service Details</div>
        <p class="gh-muted" style="font-size:11px;">Define job type, assignment, timing, and custody details.</p>
    </div>

    <div class="gh-grid-2">
        <div class="gh-field">
            <span class="gh-label">Service type *</span>
            <select wire:model="type" class="gh-select" style="width:100%;">
                <option value="">Select service type…</option>
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
                <option value="">Select priority…</option>
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
            @error('priority') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
        </div>

        <div class="gh-field">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="gh-label">Service bay</span>
                <span class="gh-hint">Optional</span>
            </div>
            <select wire:model="service_bay_id" class="gh-select" style="width:100%;">
                <option value="">Assign later…</option>
                @foreach($this->serviceBays as $bay)
                    <option value="{{ $bay->id }}">{{ $bay->name }}</option>
                @endforeach
            </select>
            @if($this->serviceBays->isEmpty())
                <span class="gh-hint" style="color:var(--gh-warning);">No available service bays</span>
            @endif
            @error('service_bay_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
        </div>

        <div class="gh-field">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="gh-label">Assign technician</span>
                <span class="gh-hint">Optional</span>
            </div>
            <select wire:model="assigned_technician_id" class="gh-select" style="width:100%;">
                <option value="">Assign later…</option>
                @foreach($this->technicians as $tech)
                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                @endforeach
            </select>
            @if($this->technicians->isEmpty())
                <span class="gh-hint" style="color:var(--gh-warning);">No technicians available</span>
            @endif
            @error('assigned_technician_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
        </div>

        <div class="gh-field">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="gh-label">Mileage in</span>
                <span class="gh-hint">km</span>
            </div>
            <input type="number" wire:model="mileage_in" placeholder="Current mileage" min="0" class="gh-input" style="width:100%;">
            @error('mileage_in') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
        </div>

        <div class="gh-field">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="gh-label">Estimated completion</span>
                <span class="gh-hint">Now or future</span>
            </div>
            <input type="datetime-local" wire:model="estimated_completion" min="{{ now()->format('Y-m-d\TH:i') }}" class="gh-input" style="width:100%;">
            @error('estimated_completion') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
        </div>

        <div class="gh-field" style="grid-column:1/-1;">
            <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                <input type="checkbox" wire:model="is_combo">
                <span>
                    <span style="font-weight:600; font-size:13px;">Combo (Service + Wash)</span>
                    <p class="gh-muted" style="font-size:11.5px; margin:2px 0 0;">Automatically queue vehicle for washing when service is complete</p>
                </span>
            </label>
        </div>
    </div>

    <div style="margin-top:20px; border-top:1px solid var(--gh-hairline); padding-top:16px;">
        <div class="gh-card__title" style="margin-bottom:10px; font-size:13px;">Items left in vehicle</div>

        <div style="display:grid; grid-template-columns:repeat(12,1fr); gap:10px; align-items:end;">
            <div class="gh-field" style="grid-column:span 3;">
                <span class="gh-label">Item</span>
                <input type="text" wire:model="left_item_name" class="gh-input" placeholder="e.g. Car jack">
            </div>
            <div class="gh-field" style="grid-column:span 2;">
                <span class="gh-label">Quantity</span>
                <input type="number" wire:model="left_item_quantity" min="0.01" step="0.01" class="gh-input">
            </div>
            <div class="gh-field" style="grid-column:span 2;">
                <span class="gh-label">Reference</span>
                <input type="text" wire:model="left_item_reference" class="gh-input" placeholder="Optional">
            </div>
            <div class="gh-field" style="grid-column:span 3;">
                <span class="gh-label">Description</span>
                <input type="text" wire:model="left_item_description" class="gh-input" placeholder="Optional notes">
            </div>
            <div style="grid-column:span 2;">
                <button type="button" wire:click="addVehicleLeftItem" class="gh-btn gh-btn--primary gh-btn--block">Add item</button>
            </div>
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
</div>

<div style="border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
    <div style="display:flex; justify-content:space-between; gap:8px;">
        <button type="button" wire:click="previousStep" class="gh-btn">← Back</button>
        <button type="button" wire:click="nextStep" class="gh-btn gh-btn--primary">Continue to items →</button>
    </div>
</div>
