<div class="gh-card gh-card--pad">
    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:8px; margin-bottom:16px;">
        <div class="gh-card__title">Customer &amp; Vehicle</div>
        <p class="gh-muted" style="font-size:11px;">Choose or create a customer, then link the vehicle for this job.</p>
    </div>

    <div class="gh-stack" style="gap:20px;">

        {{-- Customer Typeahead --}}
        <div class="gh-field" x-data="{ open: false }" x-on:focusin="open = true" x-on:focusout="setTimeout(() => { open = false }, 150)">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="gh-label">Customer *</span>
                <button type="button" wire:click="openCustomerModal" class="gh-btn gh-btn--sm">+ New customer</button>
            </div>

            @if($customer_id && $this->selectedCustomer)
                <div style="display:flex; align-items:center; gap:10px; padding:11px; background:var(--gh-success-bg); border:1px solid var(--gh-success); border-radius:var(--gh-radius);">
                    <div style="flex:1;">
                        <p style="font-weight:700; font-size:13px;">{{ $this->selectedCustomer->name }}</p>
                        <p class="gh-muted" style="font-size:12px;">
                            {{ $this->selectedCustomer->phone }}
                            @if($this->selectedCustomer->email) · {{ $this->selectedCustomer->email }} @endif
                        </p>
                    </div>
                    <button type="button" wire:click="clearCustomer" class="gh-btn gh-btn--sm" style="color:var(--gh-error);" title="Change customer">✕</button>
                </div>
            @else
                <div style="position:relative;">
                    <input type="text" wire:model.live.debounce.300ms="customerSearch" placeholder="Search by name, phone or email…" class="gh-input" style="width:100%;" autocomplete="off">

                    @if(strlen($customerSearch) > 0)
                        <div x-show="open" class="gh-card" style="position:absolute; z-index:50; top:100%; left:0; right:0; margin-top:4px; max-height:16rem; overflow-y:auto;">
                            @forelse($customers as $customer)
                                <button type="button" @mousedown.prevent wire:click="selectCustomer({{ $customer->id }})" style="width:100%; text-align:left; padding:10px 14px; border:0; border-bottom:1px solid var(--gh-hairline); background:transparent; cursor:pointer;">
                                    <p style="font-weight:600; font-size:12.5px;">{{ $customer->name }}</p>
                                    <p class="gh-muted" style="font-size:11px;">{{ $customer->phone }}@if($customer->email) · {{ $customer->email }}@endif</p>
                                </button>
                            @empty
                                <div style="padding:10px 14px; font-size:12.5px; color:var(--gh-ink-faint); font-style:italic;">No customers found for "{{ $customerSearch }}"</div>
                            @endforelse
                        </div>
                    @elseif(count($customers) > 0)
                        <div x-show="open" class="gh-card" style="position:absolute; z-index:50; top:100%; left:0; right:0; margin-top:4px; max-height:16rem; overflow-y:auto;">
                            @foreach($customers as $customer)
                                <button type="button" @mousedown.prevent wire:click="selectCustomer({{ $customer->id }})" style="width:100%; text-align:left; padding:10px 14px; border:0; border-bottom:1px solid var(--gh-hairline); background:transparent; cursor:pointer;">
                                    <p style="font-weight:600; font-size:12.5px;">{{ $customer->name }}</p>
                                    <p class="gh-muted" style="font-size:11px;">{{ $customer->phone }}@if($customer->email) · {{ $customer->email }}@endif</p>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @error('customer_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
        </div>

        {{-- Vehicle --}}
        @if($customer_id)
            <div class="gh-field"
                 x-data="{
                     search: '',
                     get filtered() {
                         const arr = Array.from($wire.vehicles ?? []);
                         if (!this.search) return arr;
                         const q = this.search.toLowerCase();
                         return arr.filter(v =>
                             (v.registration_number ?? '').toLowerCase().includes(q) ||
                             (v.make ?? '').toLowerCase().includes(q) ||
                             (v.model ?? '').toLowerCase().includes(q)
                         );
                     }
                 }">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span class="gh-label">Vehicle *</span>
                    <button type="button" wire:click="openVehicleModal" class="gh-btn gh-btn--sm">+ New vehicle</button>
                </div>

                @if($vehicle_id && $this->selectedVehicle)
                    <div style="display:flex; align-items:center; gap:10px; padding:11px; background:var(--gh-success-bg); border:1px solid var(--gh-success); border-radius:var(--gh-radius);">
                        <div style="flex:1;">
                            <p style="font-weight:700; font-size:13px;">{{ $this->selectedVehicle->registration_number }}</p>
                            <p class="gh-muted" style="font-size:12px;">
                                {{ $this->selectedVehicle->make }} {{ $this->selectedVehicle->model }}
                                @if($this->selectedVehicle->year) · {{ $this->selectedVehicle->year }} @endif
                            </p>
                        </div>
                        <button type="button" wire:click="$set('vehicle_id', null)" class="gh-btn gh-btn--sm" style="color:var(--gh-error);" title="Change vehicle">✕ Change</button>
                    </div>
                @elseif(count($vehicles) === 0)
                    <div class="gh-note"><span class="gh-note__body">No vehicles found. Click "+ New vehicle" to add one.</span></div>
                @else
                    @if(count($vehicles) > 3)
                        <input type="text" x-model="search" placeholder="Filter by plate, make or model…" class="gh-input" style="width:100%; margin-bottom:8px;" autocomplete="off">
                    @endif

                    <div class="gh-card" style="max-height:13rem; overflow-y:auto;">
                        <template x-for="v in filtered" :key="v.id">
                            <button type="button" @mousedown.prevent x-on:click="$wire.set('vehicle_id', v.id)" style="width:100%; text-align:left; padding:10px 14px; border:0; border-bottom:1px solid var(--gh-hairline); background:transparent; cursor:pointer; display:flex; align-items:center; justify-content:space-between;">
                                <div>
                                    <p style="font-weight:600; font-size:12.5px;" x-text="v.registration_number"></p>
                                    <p class="gh-muted" style="font-size:11px;" x-text="[v.make, v.model, v.year ? '(' + v.year + ')' : ''].filter(Boolean).join(' ')"></p>
                                </div>
                                <span class="gh-muted">→</span>
                            </button>
                        </template>
                        <div x-show="filtered.length === 0" style="padding:10px 14px; font-size:12.5px; color:var(--gh-ink-faint); font-style:italic;">No matching vehicles</div>
                    </div>
                @endif

                @error('vehicle_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
        @endif

        {{-- Customer Notes --}}
        <div class="gh-field">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <span class="gh-label">Customer notes</span>
                <span class="gh-hint">Optional</span>
            </div>
            <textarea wire:model="customer_notes" rows="3" placeholder="What did the customer report? Any specific issues or requests…" class="gh-input" style="width:100%;"></textarea>
        </div>
    </div>
</div>

<div style="border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
    <div style="display:flex; justify-content:flex-end;">
        <button type="button" wire:click="nextStep" class="gh-btn gh-btn--primary">Continue to service details →</button>
    </div>
</div>
