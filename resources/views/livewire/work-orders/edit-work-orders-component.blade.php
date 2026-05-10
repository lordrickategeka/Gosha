<div>
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold font-mono">{{ $workOrder->order_number }}</h1>
                <span class="badge badge-ghost">{{ ucwords(str_replace('_', ' ', $workOrder->status)) }}</span>
            </div>
            <p class="text-base-content/60 text-sm">Edit Work Order</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="alert alert-error mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Left Column: Main Form --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Vehicle & Customer (read-only) --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Vehicle & Customer</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-medium text-base-content/50 mb-1">Vehicle</p>
                            <p class="font-bold text-lg">{{ $workOrder->vehicle->registration_number }}</p>
                            <p class="text-sm text-base-content/60">{{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-base-content/50 mb-1">Customer</p>
                            <p class="font-bold">{{ $workOrder->customer->name }}</p>
                            <p class="text-sm text-base-content/60">{{ $workOrder->customer->phone }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/40 mt-2">Customer and vehicle cannot be changed after creation.</p>
                </div>
            </div>

            {{-- Job Details --}}
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
                                <option value="service">Service</option>
                                <option value="repair">Repair</option>
                                <option value="diagnostics">Diagnostics</option>
                                <option value="bodywork">Bodywork</option>
                                <option value="electrical">Electrical</option>
                                <option value="ac">A/C</option>
                                <option value="tyres">Tyres</option>
                                <option value="other">Other</option>
                            </select>
                            @error('type') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Priority --}}
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Priority *</span>
                            </label>
                            <select wire:model="priority" class="select select-bordered w-full">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            @error('priority') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
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
                            @error('service_bay_id') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
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
                            @error('assigned_technician_id') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Mileage In --}}
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Mileage In</span>
                                <span class="label-text-alt text-base-content/50">km</span>
                            </label>
                            <input type="number" wire:model="mileage_in" placeholder="e.g. 45000" min="0" class="input input-bordered w-full" />
                            @error('mileage_in') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Mileage Out --}}
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Mileage Out</span>
                                <span class="label-text-alt text-base-content/50">km</span>
                            </label>
                            <input type="number" wire:model="mileage_out" placeholder="e.g. 45100" min="0" class="input input-bordered w-full" />
                            @error('mileage_out') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Estimated Completion --}}
                        <div class="form-control sm:col-span-2">
                            <label class="label">
                                <span class="label-text font-medium">Estimated Completion</span>
                                <span class="label-text-alt text-base-content/50">Optional</span>
                            </label>
                            <input type="datetime-local" wire:model="estimated_completion" class="input input-bordered w-full" />
                            @error('estimated_completion') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Combo toggle --}}
                        <div class="form-control sm:col-span-2">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" wire:model="is_combo" class="checkbox checkbox-accent" />
                                <div>
                                    <span class="label-text font-medium">Combo Job</span>
                                    <p class="text-xs text-base-content/50">Automatically creates a wash order when the job is marked ready</p>
                                </div>
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-lg">Job Items</h2>
                    </div>

                    @error('items')
                        <div class="alert alert-error py-2 mb-4">
                            <span class="text-sm">{{ $message }}</span>
                        </div>
                    @enderror

                    {{-- Items List --}}
                    @forelse($items as $index => $item)
                        <div class="border border-base-300 rounded-lg p-4 mb-3 relative bg-base-50">

                            {{-- Remove Button --}}
                            <button
                                type="button"
                                wire:click="removeItem({{ $index }})"
                                class="btn btn-ghost btn-xs text-error absolute top-2 right-2"
                                title="Remove item"
                            >✕</button>

                            <div class="grid grid-cols-12 gap-3 items-start">

                                {{-- Item Type --}}
                                <div class="form-control col-span-12 sm:col-span-2">
                                    <label class="label py-1">
                                        <span class="label-text text-xs font-medium">Type</span>
                                    </label>
                                    <select wire:model="items.{{ $index }}.item_type" class="select select-bordered select-sm">
                                        <option value="labor">Labor</option>
                                        <option value="part">Part</option>
                                    </select>
                                </div>

                                {{-- Description --}}
                                <div class="form-control col-span-12 sm:col-span-8">
                                    <label class="label py-1">
                                        <span class="label-text text-xs font-medium">Description *</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="items.{{ $index }}.description"
                                        placeholder="Service or part description..."
                                        class="input input-bordered input-sm w-full"
                                    />
                                    @error("items.$index.description")
                                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Quantity --}}
                                <div class="form-control col-span-6 sm:col-span-2">
                                    <label class="label py-1">
                                        <span class="label-text text-xs font-medium">Qty *</span>
                                    </label>
                                    <input
                                        type="number"
                                        wire:model="items.{{ $index }}.quantity"
                                        step="0.01"
                                        min="0.01"
                                        class="input input-bordered input-sm"
                                    />
                                    @error("items.$index.quantity")
                                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-base-content/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="font-medium">No items</p>
                            <p class="text-sm">Add at least one labor or part item</p>
                        </div>
                    @endforelse

                    {{-- Add Buttons --}}
                    <div class="flex gap-2 mt-4">
                        <button type="button" wire:click="addItem('labor')" class="btn btn-sm btn-outline btn-primary">
                            + Add Labor
                        </button>
                        <button type="button" wire:click="addItem('part')" class="btn btn-sm btn-outline btn-secondary">
                            + Add Part
                        </button>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Notes</h2>
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Customer Notes</span>
                                <span class="label-text-alt text-base-content/50">Visible to customer</span>
                            </label>
                            <textarea
                                wire:model="customer_notes"
                                rows="3"
                                placeholder="Instructions from the customer, special requests..."
                                class="textarea textarea-bordered w-full"
                            ></textarea>
                            @error('customer_notes') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Technician Notes</span>
                                <span class="label-text-alt text-base-content/50">Internal only</span>
                            </label>
                            <textarea
                                wire:model="technician_notes"
                                rows="3"
                                placeholder="Findings, recommendations, internal notes..."
                                class="textarea textarea-bordered w-full"
                            ></textarea>
                            @error('technician_notes') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Summary & Actions --}}
        <div class="space-y-6">

            {{-- Summary --}}
            <div class="card bg-base-100 shadow-sm sticky top-4">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Summary</h3>

                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Items</span>
                            <span>{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-base-300 pt-2 mt-2">
                            <span class="text-base-content/60">Pricing</span>
                            <span class="badge badge-warning badge-sm">Set at quotation</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <button
                            wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn btn-primary w-full"
                        >
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                            <span wire:loading.remove wire:target="save">Save Changes</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost w-full">Cancel</a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

