<div class="gh-card gh-card--pad">
    <div class="gh-card__title" style="margin-bottom:14px;">Customer &amp; Vehicle</div>

    @if (session('customer-selector-success'))
        <div class="gh-note" style="margin-bottom:14px; background:var(--gh-success-bg); border-color:var(--gh-success);"><span class="gh-note__body" style="color:var(--gh-success);">{{ session('customer-selector-success') }}</span></div>
    @endif

    {{-- Customer Search --}}
    <div class="gh-field" style="margin-bottom:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <span class="gh-label">Customer *</span>
            @if($allowNewCustomer)
                <button type="button" wire:click="toggleNewCustomerForm" class="gh-btn gh-btn--sm">{{ $showNewCustomerForm ? '× Cancel' : '+ New customer' }}</button>
            @endif
        </div>

        <div style="position:relative;" x-data="{ open: @entangle('showCustomerDropdown').live }" @click.outside="open = false">
            <input type="text" wire:model.live.debounce.300ms="customerSearch"
                @focus="if ($el.value.length >= {{ $minSearchLength }}) open = true"
                placeholder="{{ $searchPlaceholder }}" class="gh-input" style="width:100%;" autocomplete="off">

            <ul x-show="open" x-cloak x-transition class="gh-card" style="position:absolute; z-index:10; width:100%; margin-top:4px; max-height:15rem; overflow-y:auto; list-style:none; padding:0;">
                @forelse($this->customers as $customer)
                    <li>
                        <button type="button" wire:click="selectCustomer({{ $customer->id }})" @click="open = false" style="width:100%; text-align:left; padding:8px 14px; border:0; background:transparent; cursor:pointer; border-bottom:1px solid var(--gh-hairline);">
                            <div style="font-weight:600; font-size:12.5px;">{{ $customer->name }}</div>
                            <div class="gh-muted" style="font-size:11px;">{{ $customer->phone }}</div>
                        </button>
                    </li>
                @empty
                    <li style="padding:12px 14px; font-size:12.5px; color:var(--gh-ink-faint); text-align:center;">
                        @if(strlen($customerSearch) < $minSearchLength)
                            Type at least {{ $minSearchLength }} characters…
                        @else
                            No customers found
                        @endif
                    </li>
                @endforelse
            </ul>
        </div>

        @if($customerId && $this->selectedCustomer)
            <span class="gh-hint" style="color:var(--gh-success);">✓ {{ $this->selectedCustomer->name }}</span>
        @endif
        @error('customerId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>

    {{-- New Customer Form --}}
    @if($showNewCustomerForm)
        <div style="background:var(--gh-base-200); padding:14px; border-radius:var(--gh-radius); margin-bottom:16px;" wire:transition>
            <div style="font-weight:600; font-size:13px; margin-bottom:10px;">New Customer</div>
            <div class="gh-grid-3">
                <div class="gh-field">
                    <input type="text" wire:model="newCustomerName" placeholder="Full name *" class="gh-input">
                    @error('newCustomerName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <input type="text" wire:model="newCustomerPhone" placeholder="Phone number *" class="gh-input">
                    @error('newCustomerPhone') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <input type="email" wire:model="newCustomerEmail" placeholder="Email (optional)" class="gh-input">
                    @error('newCustomerEmail') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-top:12px;">
                <button type="button" wire:click="createNewCustomer" class="gh-btn gh-btn--primary gh-btn--sm">
                    <span wire:loading.remove wire:target="createNewCustomer">Save customer</span>
                    <span wire:loading wire:target="createNewCustomer">Saving…</span>
                </button>
                <button type="button" wire:click="toggleNewCustomerForm" class="gh-btn gh-btn--sm">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Vehicle Selector --}}
    @if($customerId && $showVehicleSelector)
        <div class="gh-field">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="gh-label">Vehicle *</span>
                @if($allowNewVehicle)
                    <button type="button" wire:click="toggleNewVehicleForm" class="gh-btn gh-btn--sm">{{ $showNewVehicleForm ? '× Cancel' : '+ New vehicle' }}</button>
                @endif
            </div>
            <select wire:model.live="vehicleId" class="gh-select" style="width:100%;">
                <option value="">Select vehicle…</option>
                @foreach($this->vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }} @if($vehicle->year) ({{ $vehicle->year }}) @endif</option>
                @endforeach
            </select>
            @error('vehicleId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
        </div>

        @if($showNewVehicleForm)
            <div style="background:var(--gh-base-200); padding:14px; border-radius:var(--gh-radius); margin-top:14px;" wire:transition>
                <div style="font-weight:600; font-size:13px; margin-bottom:10px;">New Vehicle</div>
                <div class="gh-grid-3">
                    <div class="gh-field">
                        <input type="text" wire:model="newVehicleRegNumber" placeholder="Registration number *" class="gh-input">
                        @error('newVehicleRegNumber') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <input type="text" wire:model="newVehicleMake" placeholder="Make" class="gh-input">
                    <input type="text" wire:model="newVehicleModel" placeholder="Model" class="gh-input">
                    <input type="number" wire:model="newVehicleYear" placeholder="Year" class="gh-input">
                    <input type="text" wire:model="newVehicleColor" placeholder="Color" class="gh-input">
                    <input type="text" wire:model="newVehicleChassisNumber" placeholder="Chassis number" class="gh-input">
                </div>
                <div style="display:flex; gap:8px; margin-top:12px;">
                    <button type="button" wire:click="createNewVehicle" class="gh-btn gh-btn--primary gh-btn--sm">
                        <span wire:loading.remove wire:target="createNewVehicle">Save vehicle</span>
                        <span wire:loading wire:target="createNewVehicle">Saving…</span>
                    </button>
                    <button type="button" wire:click="toggleNewVehicleForm" class="gh-btn gh-btn--sm">Cancel</button>
                </div>
            </div>
        @endif
    @endif
</div>
