<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit Vehicle</h1>
            <p class="text-base-content/60">{{ $vehicle->registration_number }}</p>
        </div>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Registration Number *</span></label>
                        <input type="text" wire:model="registration_number" class="input input-bordered uppercase" />
                        @error('registration_number') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Make</span></label>
                        <input type="text" wire:model="make" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Model</span></label>
                        <input type="text" wire:model="model" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Year</span></label>
                        <input type="number" wire:model="year" class="input input-bordered" min="1900" max="2030" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Color</span></label>
                        <input type="text" wire:model="color" class="input input-bordered" />
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
                        <textarea wire:model="notes" rows="2" class="textarea textarea-bordered"></textarea>
                    </div>
                </div>
                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>
