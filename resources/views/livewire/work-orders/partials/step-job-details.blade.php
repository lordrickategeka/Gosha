<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title text-lg mb-4">Job Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Job Type --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Job Type *</span>
                </label>
                <select wire:model="type" class="select select-bordered w-full">
                    <option value="">Select job type...</option>
                    <option value="service">Service</option>
                    <option value="repair">Repair</option>
                    <option value="diagnostics">Diagnostics</option>
                    <option value="bodywork">Bodywork</option>
                    <option value="electrical">Electrical</option>
                    <option value="ac">A/C</option>
                    <option value="tyres">Tyres</option>
                    <option value="other">Other</option>
                </select>
                @error('type')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Priority --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Priority *</span>
                </label>
                <select wire:model="priority" class="select select-bordered w-full">
                    <option value="">Select priority...</option>
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                @error('priority')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Service Bay --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Service Bay</span>
                    <span class="label-text-alt text-base-content/50">Optional</span>
                </label>
                <select wire:model="service_bay_id" class="select select-bordered w-full">
                    <option value="">Assign later...</option>
                    @foreach($this->serviceBays as $bay)
                        <option value="{{ $bay->id }}">{{ $bay->name }}</option>
                    @endforeach
                </select>
                @if($this->serviceBays->isEmpty())
                    <span class="text-warning text-sm mt-1">No available service bays</span>
                @endif
                @error('service_bay_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Technician --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Assign Technician</span>
                    <span class="label-text-alt text-base-content/50">Optional</span>
                </label>
                <select wire:model="assigned_technician_id" class="select select-bordered w-full">
                    <option value="">Assign later...</option>
                    @foreach($this->technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @endforeach
                </select>
                @if($this->technicians->isEmpty())
                    <span class="text-warning text-sm mt-1">No technicians available</span>
                @endif
                @error('assigned_technician_id')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Mileage In --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Mileage In</span>
                    <span class="label-text-alt text-base-content/50">km</span>
                </label>
                <input
                    type="number"
                    wire:model="mileage_in"
                    placeholder="Current mileage"
                    min="0"
                    class="input input-bordered w-full"
                />
                @error('mileage_in')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Estimated Completion --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Estimated Completion</span>
                    <span class="label-text-alt text-base-content/50">Optional</span>
                </label>
                <input
                    type="datetime-local"
                    wire:model="estimated_completion"
                    class="input input-bordered w-full"
                />
                @error('estimated_completion')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Combo Checkbox --}}
            <div class="form-control sm:col-span-2">
                <label class="label cursor-pointer justify-start gap-3">
                    <input
                        type="checkbox"
                        wire:model="is_combo"
                        class="checkbox checkbox-primary"
                    />
                    <div>
                        <span class="label-text font-medium">Combo (Service + Wash)</span>
                        <p class="text-xs text-base-content/60">Automatically queue vehicle for washing when service is complete</p>
                    </div>
                </label>
            </div>
        </div>
    </div>
</div>

{{-- Navigation --}}
<div class="flex justify-between mt-6">
    <button type="button" wire:click="previousStep" class="btn btn-ghost">
        ← Back
    </button>
    <button type="button" wire:click="nextStep" class="btn btn-primary">
        Next: Add Items →
    </button>
</div>
