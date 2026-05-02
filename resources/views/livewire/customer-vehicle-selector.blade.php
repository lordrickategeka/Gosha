<div>
    {{-- Flash Messages --}}
    @if (session('customer-selector-success'))
        <div class="alert alert-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('customer-selector-success') }}</span>
        </div>
    @endif

    {{-- Customer Search --}}
    <div class="form-control mb-4">
        <label class="label">
            <span class="label-text font-medium">Customer *</span>
            @if($allowNewCustomer)
                <button type="button" wire:click="toggleNewCustomerForm" class="btn btn-ghost btn-xs">
                    {{ $showNewCustomerForm ? '× Cancel' : '+ New Customer' }}
                </button>
            @endif
        </label>

        <div class="relative"
             x-data="{ open: @entangle('showCustomerDropdown').live }"
             @click.outside="open = false">

            <input type="text"
                wire:model.live.debounce.300ms="customerSearch"
                @focus="if ($el.value.length >= {{ $minSearchLength }}) open = true"
                placeholder="{{ $searchPlaceholder }}"
                class="input input-bordered w-full @error('customerId') input-error @enderror"
                autocomplete="off" />

            <ul x-show="open"
                x-cloak
                x-transition
                class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                @forelse($this->customers as $customer)
                    <li>
                        <button type="button"
                            wire:click="selectCustomer({{ $customer->id }})"
                            @click="open = false"
                            class="w-full px-4 py-2 text-left hover:bg-base-200 focus:bg-base-200 transition-colors">
                            <div class="font-medium">{{ $customer->name }}</div>
                            <div class="text-sm text-base-content/60">{{ $customer->phone }}</div>
                        </button>
                    </li>
                @empty
                    <li class="px-4 py-3 text-sm text-base-content/50 text-center">
                        @if(strlen($customerSearch) < $minSearchLength)
                            Type at least {{ $minSearchLength }} characters...
                        @else
                            No customers found
                        @endif
                    </li>
                @endforelse
            </ul>
        </div>

        @if($customerId && $this->selectedCustomer)
            <span class="label-text-alt text-success mt-1">✓ {{ $this->selectedCustomer->name }}</span>
        @endif

        @error('customerId')
            <span class="label-text-alt text-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- New Customer Form --}}
    @if($showNewCustomerForm)
        <div class="bg-base-200 p-4 rounded-lg mb-4" wire:transition>
            <h3 class="font-medium mb-3">New Customer</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="form-control">
                    <input type="text"
                        wire:model="newCustomerName"
                        placeholder="Full Name *"
                        class="input input-bordered input-sm @error('newCustomerName') input-error @enderror" />
                    @error('newCustomerName')
                        <span class="label-text-alt text-error text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-control">
                    <input type="text"
                        wire:model="newCustomerPhone"
                        placeholder="Phone Number *"
                        class="input input-bordered input-sm @error('newCustomerPhone') input-error @enderror" />
                    @error('newCustomerPhone')
                        <span class="label-text-alt text-error text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-control">
                    <input type="email"
                        wire:model="newCustomerEmail"
                        placeholder="Email (optional)"
                        class="input input-bordered input-sm @error('newCustomerEmail') input-error @enderror" />
                    @error('newCustomerEmail')
                        <span class="label-text-alt text-error text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button type="button" wire:click="createNewCustomer" class="btn btn-primary btn-sm">
                    <span wire:loading.remove wire:target="createNewCustomer">Save Customer</span>
                    <span wire:loading wire:target="createNewCustomer" class="loading loading-spinner loading-xs"></span>
                </button>
                <button type="button" wire:click="toggleNewCustomerForm" class="btn btn-ghost btn-sm">Cancel</button>
            </div>
        </div>
    @endif

    {{-- Vehicle Selector (Only show if customer is selected and feature is enabled) --}}
    @if($customerId && $showVehicleSelector)
        <div class="form-control">
            <label class="label">
                <span class="label-text font-medium">Vehicle *</span>
                @if($allowNewVehicle)
                    <button type="button" wire:click="toggleNewVehicleForm" class="btn btn-ghost btn-xs">
                        {{ $showNewVehicleForm ? '× Cancel' : '+ New Vehicle' }}
                    </button>
                @endif
            </label>
            <select wire:model.live="vehicleId" class="select select-bordered w-full @error('vehicleId') select-error @enderror">
                <option value="">Select vehicle...</option>
                @foreach($this->vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">
                        {{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}
                        @if($vehicle->year) ({{ $vehicle->year }}) @endif
                    </option>
                @endforeach
            </select>
            @error('vehicleId')
                <span class="label-text-alt text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- New Vehicle Form --}}
        @if($showNewVehicleForm)
            <div class="bg-base-200 p-4 rounded-lg mt-4" wire:transition>
                <h3 class="font-medium mb-3">New Vehicle</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="form-control">
                        <input type="text"
                            wire:model="newVehicleRegNumber"
                            placeholder="Registration Number *"
                            class="input input-bordered input-sm @error('newVehicleRegNumber') input-error @enderror" />
                        @error('newVehicleRegNumber')
                            <span class="label-text-alt text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <input type="text" wire:model="newVehicleMake" placeholder="Make" class="input input-bordered input-sm" />
                    <input type="text" wire:model="newVehicleModel" placeholder="Model" class="input input-bordered input-sm" />
                    <input type="number" wire:model="newVehicleYear" placeholder="Year" class="input input-bordered input-sm" />
                    <input type="text" wire:model="newVehicleColor" placeholder="Color" class="input input-bordered input-sm" />
                    <input type="text" wire:model="newVehicleChassisNumber" placeholder="Chassis Number" class="input input-bordered input-sm" />
                </div>
                <div class="flex gap-2 mt-3">
                    <button type="button" wire:click="createNewVehicle" class="btn btn-primary btn-sm">
                        <span wire:loading.remove wire:target="createNewVehicle">Save Vehicle</span>
                        <span wire:loading wire:target="createNewVehicle" class="loading loading-spinner loading-xs"></span>
                    </button>
                    <button type="button" wire:click="toggleNewVehicleForm" class="btn btn-ghost btn-sm">Cancel</button>
                </div>
            </div>
        @endif
    @endif
</div>
