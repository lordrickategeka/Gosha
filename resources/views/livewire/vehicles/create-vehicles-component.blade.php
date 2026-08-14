<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('vehicles.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Add Vehicle</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">Register a new vehicle</p>
        </div>
    </div>

    <form wire:submit="save" style="max-width:56rem; display:flex; flex-direction:column; gap:16px;">
        <!-- Owner -->
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Owner</div>
            <div class="gh-field" style="max-width:28rem; position:relative;">
                <span class="gh-label">Owner *</span>
                <input type="text" wire:model.live.debounce.300ms="customerSearch" placeholder="Search customer…" class="gh-input" style="width:100%;" autocomplete="off">
                @if($showCustomerDropdown && $this->customers->count() > 0)
                    <ul class="gh-card" style="position:absolute; z-index:10; top:100%; left:0; right:0; margin-top:4px; max-height:15rem; overflow-y:auto; list-style:none; padding:0;">
                        @foreach($this->customers as $customer)
                            <li><button type="button" wire:click="selectCustomer({{ $customer->id }})" style="width:100%; text-align:left; padding:8px 14px; border:0; background:transparent; cursor:pointer; border-bottom:1px solid var(--gh-hairline); font-size:12.5px;">{{ $customer->name }} - {{ $customer->phone }}</button></li>
                        @endforeach
                    </ul>
                @endif
                @error('customer_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Vehicle Information -->
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Vehicle Information</div>
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">Registration number *</span>
                    <input type="text" wire:model="registration_number" class="gh-input" style="width:100%; text-transform:uppercase;" placeholder="UAA 123B">
                    @error('registration_number') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Status</span>
                    <select wire:model="status" class="gh-select" style="width:100%;">
                        <option value="active">Active</option>
                        <option value="in_shop">In Shop</option>
                        <option value="decommissioned">Decommissioned</option>
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Make</span>
                    <input type="text" wire:model="make" class="gh-input" style="width:100%;" placeholder="Toyota">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Model</span>
                    <input type="text" wire:model="model" class="gh-input" style="width:100%;" placeholder="Hilux">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Year</span>
                    <input type="number" wire:model="year" class="gh-input" style="width:100%;" placeholder="2024" min="1900" max="2035">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Color</span>
                    <input type="text" wire:model="color" class="gh-input" style="width:100%;" placeholder="Silver">
                </div>
            </div>
        </div>

        <!-- VIN & Engine -->
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">VIN &amp; Engine</div>
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">VIN</span>
                    <input type="text" wire:model="vin" class="gh-input" style="width:100%; text-transform:uppercase;" placeholder="1HGBH41JXMKN10986" maxlength="17">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Engine number</span>
                    <input type="text" wire:model="engine_number" class="gh-input" style="width:100%; text-transform:uppercase;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Engine code</span>
                    <input type="text" wire:model="engine_code" class="gh-input" style="width:100%; text-transform:uppercase;" placeholder="2GD-FTV">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Displacement (L)</span>
                    <input type="number" step="0.1" wire:model="engine_displacement" class="gh-input" style="width:100%;" placeholder="2.0" min="0" max="10">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Fuel type</span>
                    <select wire:model="fuel_type" class="gh-select" style="width:100%;">
                        <option value="gasoline">Gasoline</option>
                        <option value="diesel">Diesel</option>
                        <option value="electric">Electric</option>
                        <option value="hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Mileage</span>
                    <input type="number" wire:model="mileage" class="gh-input" style="width:100%;" placeholder="0" min="0">
                </div>
            </div>
        </div>

        <!-- Transmission & Drivetrain -->
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Transmission &amp; Drivetrain</div>
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">Transmission</span>
                    <select wire:model="transmission_type" class="gh-select" style="width:100%;">
                        <option value="automatic">Automatic</option>
                        <option value="manual">Manual</option>
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Drivetrain</span>
                    <select wire:model="drivetrain_type" class="gh-select" style="width:100%;">
                        <option value="">Select</option>
                        <option value="fwd">FWD</option>
                        <option value="rwd">RWD</option>
                        <option value="awd">AWD</option>
                        <option value="4wd">4WD</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Financial -->
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Financial &amp; Lifecycle</div>
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">In-service date</span>
                    <input type="date" wire:model="in_service_date" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Acquisition date</span>
                    <input type="date" wire:model="acquisition_date" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Acquisition cost</span>
                    <input type="number" step="0.01" wire:model="acquisition_cost" class="gh-input" style="width:100%;" placeholder="0.00" min="0">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Current value</span>
                    <input type="number" step="0.01" wire:model="current_value" class="gh-input" style="width:100%;" placeholder="0.00" min="0">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Ownership</span>
                    <select wire:model="ownership_status" class="gh-select" style="width:100%;">
                        <option value="owned">Owned</option>
                        <option value="leased">Leased</option>
                        <option value="financed">Financed</option>
                        <option value="customer_owned">Customer Owned</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:10px;">Additional Notes</div>
            <textarea wire:model="notes" rows="4" class="gh-input" style="width:100%;" placeholder="Any notes about the vehicle…"></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:8px;">
            <a href="{{ route('vehicles.index') }}" class="gh-btn">Cancel</a>
            <button type="submit" class="gh-btn gh-btn--primary">Save vehicle</button>
        </div>
    </form>
</div>
