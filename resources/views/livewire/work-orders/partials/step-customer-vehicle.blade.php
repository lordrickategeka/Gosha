<div class="card bg-base-100 shadow-sm border border-base-300">
    <div class="card-body">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <h2 class="card-title text-lg">Customer &amp; Vehicle</h2>
            <p class="text-xs text-base-content/60">Choose or create a customer, then link the vehicle for this job.</p>
        </div>

        <div class="space-y-6">

            {{-- ── Customer Typeahead ── --}}
            <div class="form-control"
                 x-data="{ open: false }"
                 x-on:focusin="open = true"
                 x-on:focusout="setTimeout(() => { open = false }, 150)"
            >
                <label class="label">
                    <span class="label-text font-medium">Customer *</span>
                    <button type="button" wire:click="openCustomerModal" class="btn btn-ghost btn-xs">+ New Customer</button>
                </label>

                {{-- Show selected chip OR search input --}}
                @if($customer_id && $this->selectedCustomer)
                    <div class="flex items-center gap-3 p-3 bg-success/10 border border-success/30 rounded-lg">
                        <div class="flex-1">
                            <p class="font-semibold">{{ $this->selectedCustomer->name }}</p>
                            <p class="text-sm text-base-content/60">
                                {{ $this->selectedCustomer->phone }}
                                @if($this->selectedCustomer->email) &bull; {{ $this->selectedCustomer->email }} @endif
                            </p>
                        </div>
                        <button type="button" wire:click="clearCustomer" class="btn btn-ghost btn-xs text-error" title="Change customer">✕</button>
                    </div>
                @else
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="customerSearch"
                            placeholder="Search by name, phone or email..."
                            class="input input-bordered w-full"
                            autocomplete="off"
                        />

                        {{-- Results dropdown --}}
                        @if(strlen($customerSearch) > 0)
                            <div
                                x-show="open"
                                class="absolute z-50 top-full left-0 right-0 mt-1 bg-base-100 border border-base-300 rounded-lg shadow-xl max-h-64 overflow-y-auto"
                            >
                                @forelse($customers as $customer)
                                    <button
                                        type="button"
                                        @mousedown.prevent
                                        wire:click="selectCustomer({{ $customer->id }})"
                                        class="w-full text-left px-4 py-3 hover:bg-base-200 flex items-start justify-between border-b border-base-200 last:border-0"
                                    >
                                        <div>
                                            <p class="font-medium text-sm">{{ $customer->name }}</p>
                                            <p class="text-xs text-base-content/50">{{ $customer->phone }}@if($customer->email) &bull; {{ $customer->email }}@endif</p>
                                        </div>
                                    </button>
                                @empty
                                    <div class="px-4 py-3 text-sm text-base-content/50 italic">No customers found for "{{ $customerSearch }}"</div>
                                @endforelse
                            </div>
                        @elseif(count($customers) > 0)
                            <div
                                x-show="open"
                                class="absolute z-50 top-full left-0 right-0 mt-1 bg-base-100 border border-base-300 rounded-lg shadow-xl max-h-64 overflow-y-auto"
                            >
                                @foreach($customers as $customer)
                                    <button
                                        type="button"
                                        @mousedown.prevent
                                        wire:click="selectCustomer({{ $customer->id }})"
                                        class="w-full text-left px-4 py-3 hover:bg-base-200 flex items-start justify-between border-b border-base-200 last:border-0"
                                    >
                                        <div>
                                            <p class="font-medium text-sm">{{ $customer->name }}</p>
                                            <p class="text-xs text-base-content/50">{{ $customer->phone }}@if($customer->email) &bull; {{ $customer->email }}@endif</p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @error('customer_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- ── Vehicle (only after customer selected) ── --}}
            @if($customer_id)
                <div class="form-control"
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
                     }"
                >
                    <label class="label">
                        <span class="label-text font-medium">Vehicle *</span>
                        <button type="button" wire:click="openVehicleModal" class="btn btn-ghost btn-xs">+ New Vehicle</button>
                    </label>

                    @if($vehicle_id && $this->selectedVehicle)
                        <div class="flex items-center gap-3 p-3 bg-success/10 border border-success/30 rounded-lg">
                            <div class="flex-1">
                                <p class="font-semibold">{{ $this->selectedVehicle->registration_number }}</p>
                                <p class="text-sm text-base-content/60">
                                    {{ $this->selectedVehicle->make }} {{ $this->selectedVehicle->model }}
                                    @if($this->selectedVehicle->year) &bull; {{ $this->selectedVehicle->year }} @endif
                                </p>
                            </div>
                            <button type="button" wire:click="$set('vehicle_id', null)" class="btn btn-ghost btn-xs text-error" title="Change vehicle">✕ Change</button>
                        </div>
                    @elseif(count($vehicles) === 0)
                        <div class="alert alert-warning py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm">No vehicles found. Click "+ New Vehicle" to add one.</span>
                        </div>
                    @else
                        {{-- Filter input (only shown when > 3 vehicles) --}}
                        @if(count($vehicles) > 3)
                            <input
                                type="text"
                                x-model="search"
                                placeholder="Filter by plate, make or model..."
                                class="input input-bordered input-sm w-full mb-2"
                                autocomplete="off"
                            />
                        @endif

                        {{-- Scrollable vehicle card list --}}
                        <div class="border border-base-300 rounded-lg overflow-hidden max-h-52 overflow-y-auto">
                            <template x-for="v in filtered" :key="v.id">
                                <button
                                    type="button"
                                    @mousedown.prevent
                                    x-on:click="$wire.set('vehicle_id', v.id)"
                                    class="w-full text-left px-4 py-3 hover:bg-primary/10 flex items-center justify-between border-b border-base-200 last:border-0 transition-colors"
                                >
                                    <div>
                                        <p class="font-medium text-sm" x-text="v.registration_number"></p>
                                        <p class="text-xs text-base-content/50" x-text="[v.make, v.model, v.year ? '(' + v.year + ')' : ''].filter(Boolean).join(' ')"></p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </template>
                            <div x-show="filtered.length === 0" class="px-4 py-3 text-sm text-base-content/50 italic">No matching vehicles</div>
                        </div>
                    @endif

                    @error('vehicle_id')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            {{-- Customer Notes --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Customer Notes</span>
                    <span class="label-text-alt text-base-content/50">Optional</span>
                </label>
                <textarea
                    wire:model="customer_notes"
                    rows="3"
                    placeholder="What did the customer report? Any specific issues or requests..."
                    class="textarea textarea-bordered w-full"
                ></textarea>
            </div>
        </div>
    </div>
</div>

<div class="mt-5 border-t border-base-300 pt-4">
    <div class="flex justify-end">
        <button type="button" wire:click="nextStep" class="btn btn-primary">
            Continue to Service Details
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</div>
