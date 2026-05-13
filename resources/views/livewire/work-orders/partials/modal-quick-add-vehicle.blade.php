{{-- Quick Add Vehicle Modal --}}
<input type="checkbox" id="vehicle-modal" class="modal-toggle" @if($showVehicleModal) checked @endif />
<div class="modal" role="dialog">
    <div class="modal-box app-modal-shell">
        <h3 class="font-bold text-lg mb-4">Quick Add Vehicle</h3>

        <div class="space-y-3">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Registration Number *</span>
                </label>
                <input
                    type="text"
                    wire:model="newVehicleRegNumber"
                    placeholder="e.g., UAH 123A"
                    class="input input-bordered w-full uppercase"
                />
                @error('newVehicleRegNumber')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Make</span>
                        <span class="label-text-alt text-base-content/50">Optional</span>
                    </label>
                    <input
                        type="text"
                        wire:model="newVehicleMake"
                        placeholder="e.g., Toyota"
                        class="input input-bordered w-full"
                    />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Model</span>
                        <span class="label-text-alt text-base-content/50">Optional</span>
                    </label>
                    <input
                        type="text"
                        wire:model="newVehicleModel"
                        placeholder="e.g., Corolla"
                        class="input input-bordered w-full"
                    />
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Year</span>
                    <span class="label-text-alt text-base-content/50">Optional</span>
                </label>
                <input
                    type="number"
                    wire:model="newVehicleYear"
                    placeholder="e.g., 2020"
                    min="1900"
                    max="{{ date('Y') + 1 }}"
                    class="input input-bordered w-full"
                />
                @error('newVehicleYear')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="modal-action app-modal-actions">
            <button
                type="button"
                wire:click="closeVehicleModal"
                class="btn btn-ghost"
            >
                Cancel
            </button>
            <button
                type="button"
                wire:click="saveNewVehicle"
                class="btn btn-primary"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="saveNewVehicle">Add Vehicle</span>
                <span wire:loading wire:target="saveNewVehicle" class="loading loading-spinner loading-sm"></span>
            </button>
        </div>
    </div>
    <label class="modal-backdrop app-modal-backdrop" for="vehicle-modal" wire:click="closeVehicleModal">Close</label>
</div>
