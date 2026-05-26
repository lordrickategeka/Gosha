<div class="card bg-base-100 shadow-sm border border-base-300">
    <div class="card-body">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <h2 class="card-title text-lg">Service Details</h2>
            <p class="text-xs text-base-content/60">Define job type, assignment, timing, and custody details.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Service Type --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Service Type *</span>
                </label>
                <select wire:model="type" class="select select-bordered w-full">
                    <option value="">Select service type...</option>
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
                    <span class="label-text-alt text-base-content/50">Now or future</span>
                </label>
                <input
                    type="datetime-local"
                    wire:model="estimated_completion"
                    min="{{ now()->format('Y-m-d\\TH:i') }}"
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

        <div class="mt-6 border-t border-base-200 pt-5">
            <h3 class="font-medium mb-3">Items Left In Vehicle</h3>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="form-control md:col-span-3">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Item</span>
                    </label>
                    <input type="text" wire:model="left_item_name" class="input input-bordered input-sm" placeholder="e.g. Car jack" />
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Quantity</span>
                    </label>
                    <input type="number" wire:model="left_item_quantity" min="0.01" step="0.01" class="input input-bordered input-sm" />
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Reference</span>
                    </label>
                    <input type="text" wire:model="left_item_reference" class="input input-bordered input-sm" placeholder="Optional" />
                </div>
                <div class="form-control md:col-span-3">
                    <label class="label py-1">
                        <span class="label-text text-xs font-medium">Description</span>
                    </label>
                    <input type="text" wire:model="left_item_description" class="input input-bordered input-sm" placeholder="Optional notes" />
                </div>
                <div class="form-control md:col-span-2">
                    <button type="button" wire:click="addVehicleLeftItem" class="btn btn-primary btn-sm w-full">Add Item</button>
                </div>
            </div>

            @if(count($vehicle_left_items) > 0)
                <div class="mt-4 overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicle_left_items as $index => $leftItem)
                                <tr>
                                    <td>{{ $leftItem['item_name'] }}</td>
                                    <td>{{ $leftItem['quantity'] }}</td>
                                    <td>{{ $leftItem['reference'] ?: '—' }}</td>
                                    <td>{{ $leftItem['description'] ?: '—' }}</td>
                                    <td class="text-right">
                                        <button type="button" wire:click="removeVehicleLeftItem({{ $index }})" class="btn btn-ghost btn-xs text-error">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="mt-5 border-t border-base-300 pt-4">
    <div class="flex justify-between gap-2">
        <button type="button" wire:click="previousStep" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </button>
        <button type="button" wire:click="nextStep" class="btn btn-primary">
            Continue to Items
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</div>
