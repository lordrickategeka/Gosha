<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('appointments.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">New Appointment</h1>
            <p class="text-base-content/60">Schedule a service appointment</p>
        </div>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <!-- Customer -->
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-medium">Customer *</span></label>
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

                @if($customer_id)
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text font-medium">Vehicle *</span></label>
                        <select wire:model="vehicle_id" class="select select-bordered">
                            <option value="">Select vehicle...</option>
                            @foreach($this->vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Service Type *</span></label>
                        <select wire:model="type" class="select select-bordered">
                            <option value="service">General Service</option>
                            <option value="repair">Repair</option>
                            <option value="wash">Wash</option>
                            <option value="diagnostics">Diagnostics</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Est. Duration (mins)</span></label>
                        <select wire:model="estimated_duration" class="select select-bordered">
                            <option value="30">30 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="120">2 hours</option>
                            <option value="180">3 hours</option>
                            <option value="240">4 hours</option>
                            <option value="480">Full day</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date *</span></label>
                        <input type="date" wire:model="scheduled_date" class="input input-bordered" min="{{ now()->format('Y-m-d') }}" />
                        @error('scheduled_date') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Time *</span></label>
                        <input type="time" wire:model="scheduled_time" class="input input-bordered" />
                    </div>
                </div>

                <div class="form-control mt-4">
                    <label class="label"><span class="label-text font-medium">Notes</span></label>
                    <textarea wire:model="notes" rows="2" class="textarea textarea-bordered" placeholder="Any special requests or notes..."></textarea>
                </div>

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('appointments.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Schedule Appointment</button>
                </div>
            </div>
        </div>
    </form>
</div>
