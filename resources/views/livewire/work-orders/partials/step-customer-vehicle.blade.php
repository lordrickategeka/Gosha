<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title text-lg mb-4">Customer &amp; Vehicle</h2>

        <div class="space-y-4">
            {{-- Customer Selection --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Customer *</span>
                    <button type="button" wire:click="openCustomerModal" class="btn btn-ghost btn-xs">
                        + New Customer
                    </button>
                </label>

                {{-- Search Input --}}
                <input
                    type="text"
                    wire:model.live.debounce.300ms="customerSearch"
                    placeholder="Search customers by name, phone, or email..."
                    class="input input-bordered w-full mb-2"
                />

                {{-- Customer Select --}}
                <select wire:model.live="customer_id" class="select select-bordered w-full">
                    <option value="">Select a customer...</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">
                            {{ $customer->name }} - {{ $customer->phone }}
                        </option>
                    @endforeach
                </select>

                @error('customer_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror

                {{-- Selected Customer Summary --}}
                @if($customer_id && $this->selectedCustomer)
                    <div class="mt-2 p-3 bg-success/10 border border-success/30 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <div class="font-medium">{{ $this->selectedCustomer->name }}</div>
                                <div class="text-sm text-base-content/70">
                                    {{ $this->selectedCustomer->phone }}
                                    @if($this->selectedCustomer->email)
                                        • {{ $this->selectedCustomer->email }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Vehicle Selection (only show when customer selected) --}}
            @if($customer_id)
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Vehicle *</span>
                        <button type="button" wire:click="openVehicleModal" class="btn btn-ghost btn-xs">
                            + New Vehicle
                        </button>
                    </label>

                    <select wire:model.live="vehicle_id" class="select select-bordered w-full">
                        <option value="">Select a vehicle...</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->registration_number }}
                                @if($vehicle->make || $vehicle->model)
                                    - {{ $vehicle->make }} {{ $vehicle->model }}
                                @endif
                                @if($vehicle->year)
                                    ({{ $vehicle->year }})
                                @endif
                            </option>
                        @endforeach
                    </select>

                    @error('vehicle_id')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror

                    @if($vehicles->isEmpty())
                        <div class="alert alert-warning mt-2 py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm">This customer has no vehicles. Click "+ New Vehicle" to add one.</span>
                        </div>
                    @endif

                    {{-- Selected Vehicle Summary --}}
                    @if($vehicle_id && $this->selectedVehicle)
                        <div class="mt-2 p-3 bg-success/10 border border-success/30 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <div class="font-medium">{{ $this->selectedVehicle->registration_number }}</div>
                                    <div class="text-sm text-base-content/70">
                                        {{ $this->selectedVehicle->make }} {{ $this->selectedVehicle->model }}
                                        @if($this->selectedVehicle->year)
                                            • {{ $this->selectedVehicle->year }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
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

{{-- Navigation --}}
<div class="flex justify-end mt-6">
    <button type="button" wire:click="nextStep" class="btn btn-primary">
        Next: Job Details →
    </button>
</div>
