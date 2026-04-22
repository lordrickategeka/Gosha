<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('vehicles.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Add Vehicle</h1>
            <p class="text-base-content/60">Register a new vehicle</p>
        </div>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <!-- Owner -->
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-medium">Owner *</span></label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="customerSearch" placeholder="Search customer..." class="input input-bordered w-full" autocomplete="off" />
                        @if($showCustomerDropdown && $this->customers->count() > 0)
                            <ul class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                @foreach($this->customers as $customer)
                                    <li><button type="button" wire:click="selectCustomer({{ $customer->id }})" class="w-full px-4 py-2 text-left hover:bg-base-200">{{ $customer->name }} - {{ $customer->phone }}</button></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @error('customer_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Registration Number *</span></label>
                        <input type="text" wire:model="registration_number" class="input input-bordered uppercase" placeholder="UAA 123B" />
                        @error('registration_number') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Make</span></label>
                        <input type="text" wire:model="make" class="input input-bordered" placeholder="Toyota" />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Model</span></label>
                        <input type="text" wire:model="model" class="input input-bordered" placeholder="Corolla" />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Year</span></label>
                        <input type="number" wire:model="year" class="input input-bordered" placeholder="2020" min="1900" max="2030" />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Color</span></label>
                        <input type="text" wire:model="color" class="input input-bordered" placeholder="Silver" />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">VIN / Chassis</span></label>
                        <input type="text" wire:model="vin" class="input input-bordered" />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Fuel Type</span></label>
                        <select wire:model="fuel_type" class="select select-bordered">
                            <option value="petrol">Petrol</option>
                            <option value="diesel">Diesel</option>
                            <option value="electric">Electric</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Transmission</span></label>
                        <select wire:model="transmission" class="select select-bordered">
                            <option value="automatic">Automatic</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>

                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Notes</span></label>
                        <textarea wire:model="notes" rows="2" class="textarea textarea-bordered" placeholder="Any notes about the vehicle..."></textarea>
                    </div>
                </div>

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Vehicle</button>
                </div>
            </div>
        </div>
    </form>
</div>
