<div>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('work-orders.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">New Work Order</h1>
            <p class="text-base-content/60">Create a new service or repair job</p>
        </div>
    </div>

    <!-- Step Progress -->
    <div class="flex items-center mb-8 gap-0">
        @foreach ([1 => 'Customer & Vehicle', 2 => 'Job Details', 3 => 'Items', 4 => 'Review'] as $step => $label)
            <div class="flex items-center flex-1 {{ !$loop->last ? '' : 'flex-none' }}">
                <div class="flex flex-col items-center">
                    <div
                        class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm
                        {{ $currentStep > $step ? 'bg-success text-success-content' : ($currentStep === $step ? 'bg-primary text-primary-content' : 'bg-base-300 text-base-content/50') }}">
                        @if ($currentStep > $step)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        @else
                            {{ $step }}
                        @endif
                    </div>
                    <span
                        class="text-xs mt-1 hidden sm:block {{ $currentStep === $step ? 'text-primary font-medium' : 'text-base-content/50' }}">{{ $label }}</span>
                </div>
                @if (!$loop->last)
                    <div class="flex-1 h-0.5 mx-2 {{ $currentStep > $step ? 'bg-success' : 'bg-base-300' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    @if (session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    {{-- STEP 1: Customer & Vehicle --}}
    @if ($currentStep === 1)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <!-- Customer & Vehicle Card -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Customer &amp; Vehicle</h2>

                        <!-- Customer Search -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Customer *</span>
                                <button type="button" wire:click="openNewCustomerForm" class="btn btn-ghost btn-xs">
                                    + New Customer
                                </button>
                            </label>
                            <div class="relative"
                                 x-data="{ open: false }"
                                 @click.outside="open = false"
                                 @close-customer-dropdown.window="open = false">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="customerSearch"
                                    @focus="open = true"
                                    @input="open = true"
                                    placeholder="Search by name or phone..."
                                    class="input input-bordered w-full"
                                    autocomplete="off"
                                />
                                <ul x-show="open" x-cloak
                                    class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    @forelse($this->customers as $customer)
                                        <li>
                                            <button
                                                type="button"
                                                @mousedown.prevent
                                                wire:click="selectCustomer({{ $customer->id }})"
                                                class="w-full px-4 py-2 text-left hover:bg-base-200"
                                            >
                                                <div class="font-medium">{{ $customer->name }}</div>
                                                <div class="text-sm text-base-content/60">{{ $customer->phone }}</div>
                                            </button>
                                        </li>
                                    @empty
                                        <li class="px-4 py-3 text-sm text-base-content/50 text-center">
                                            {{ strlen($customerSearch) < 2 ? 'Type at least 2 characters...' : 'No customers found' }}
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                            @error('customer_id') <span class="label-text-alt text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Inline New Customer Form -->
                        @if($showNewCustomerForm)
                            <div class="bg-base-200 p-4 rounded-lg mb-4">
                                <h3 class="font-medium mb-3">New Customer</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <input type="text" wire:model="newCustomerName" placeholder="Full Name *" class="input input-bordered input-sm w-full" />
                                        @error('newCustomerName') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input type="text" wire:model="newCustomerPhone" placeholder="Phone *" class="input input-bordered input-sm w-full" />
                                        @error('newCustomerPhone') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="flex gap-2 mt-3">
                                    <button type="button" wire:click="createNewCustomer" class="btn btn-primary btn-sm">Save Customer</button>
                                    <button type="button" wire:click="hideNewCustomerForm" class="btn btn-ghost btn-sm">Cancel</button>
                                </div>
                            </div>
                        @endif

                        <!-- Vehicle Selection (only shown once customer is chosen) -->
                        @if($customer_id)
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Vehicle *</span>
                                    <button type="button" wire:click="openNewVehicleForm" class="btn btn-ghost btn-xs">
                                        + New Vehicle
                                    </button>
                                </label>
                                <select wire:model="vehicle_id" class="select select-bordered w-full">
                                    <option value="">Select vehicle...</option>
                                    @foreach($this->vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">
                                            {{ $vehicle->registration_number }}
                                            @if($vehicle->make || $vehicle->model)
                                                — {{ trim($vehicle->make . ' ' . $vehicle->model) }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id') <span class="label-text-alt text-error mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            @if($showNewVehicleForm)
                                <div class="bg-base-200 p-4 rounded-lg mt-4">
                                    <h3 class="font-medium mb-3">New Vehicle</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <input type="text" wire:model="newVehicleRegNumber" placeholder="Reg Number *" class="input input-bordered input-sm w-full uppercase" />
                                            @error('newVehicleRegNumber') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                        <input type="text" wire:model="newVehicleMake" placeholder="Make (e.g. Toyota)" class="input input-bordered input-sm w-full" />
                                        <input type="text" wire:model="newVehicleModel" placeholder="Model (e.g. Corolla)" class="input input-bordered input-sm w-full" />
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="button" wire:click="createNewVehicle" class="btn btn-primary btn-sm">Save Vehicle</button>
                                        <button type="button" wire:click="hideNewVehicleForm" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                @if ($showNewCustomerForm)
                    <div class="bg-base-200 p-4 rounded-lg mb-4">
                        <h3 class="font-medium mb-3">New Customer</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <input type="text" wire:model="newCustomerName" placeholder="Name *"
                                class="input input-bordered input-sm" />
                            <input type="text" wire:model="newCustomerPhone" placeholder="Phone *"
                                class="input input-bordered input-sm" />
                            <input type="email" wire:model="newCustomerEmail" placeholder="Email"
                                class="input input-bordered input-sm" />
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button type="button" wire:click="createNewCustomer" class="btn btn-primary btn-sm">Save
                                Customer</button>
                            <button type="button" wire:click="hideNewCustomerForm"
                                class="btn btn-ghost btn-sm">Cancel</button>
                        </div>
                    </div>
                @endif

                @if ($customer_id)
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Vehicle *</span>
                            <button type="button" wire:click="openNewVehicleForm" class="btn btn-ghost btn-xs">+ New
                                Vehicle</button>
                        </label>
                        <select wire:model="vehicle_id" class="select select-bordered w-full">
                            <option value="">Select vehicle...</option>
                            @foreach ($this->vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}
                                    @if ($vehicle->chassis_number)
                                        ({{ $vehicle->chassis_number }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($showNewVehicleForm)
                        <div class="bg-base-200 p-4 rounded-lg mt-4">
                            <h3 class="font-medium mb-3">New Vehicle</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <input type="text" wire:model="newVehicleRegNumber" placeholder="Reg Number *"
                                    class="input input-bordered input-sm" />
                                <input type="text" wire:model="newVehicleMake" placeholder="Make"
                                    class="input input-bordered input-sm" />
                                <input type="text" wire:model="newVehicleModel" placeholder="Model"
                                    class="input input-bordered input-sm" />
                                <input type="number" wire:model="newVehicleYear" placeholder="Year"
                                    class="input input-bordered input-sm" />
                                <input type="text" wire:model="newVehicleColor" placeholder="Color"
                                    class="input input-bordered input-sm" />
                                <input type="text" wire:model="newVehicleChassisNumber"
                                    placeholder="Chassis Number" class="input input-bordered input-sm" />
                            </div>
                            <div class="flex gap-2 mt-3">
                                <button type="button" wire:click="createNewVehicle"
                                    class="btn btn-primary btn-sm">Save Vehicle</button>
                                <button type="button" wire:click="hideNewVehicleForm"
                                    class="btn btn-ghost btn-sm">Cancel</button>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="form-control mt-4">
                    <label class="label"><span class="label-text font-medium">Customer Notes</span></label>
                    <textarea wire:model="customer_notes" rows="3"
                        placeholder="What did the customer report? Any specific issues or requests..."
                        class="textarea textarea-bordered w-full"></textarea>
                </div>
            </div>
        </div>
        <div class="flex justify-end mt-6">
            <button type="button" wire:click="nextStep" class="btn btn-primary">Next: Job Details →</button>
        </div>
    @endif

    {{-- STEP 2: Job Details --}}
    @if ($currentStep === 2)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Job Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Job Type</span></label>
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
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Priority</span></label>
                        <select wire:model="priority" class="select select-bordered w-full">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Assign Bay</span></label>
                        <select wire:model="service_bay_id" class="select select-bordered w-full">
                            <option value="">Select later...</option>
                            @foreach ($this->serviceBays as $bay)
                                <option value="{{ $bay->id }}">{{ $bay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Assign Technician</span></label>
                        <select wire:model="assigned_technician_id" class="select select-bordered w-full">
                            <option value="">Assign later...</option>
                            @foreach ($this->technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Mileage In</span></label>
                        <input type="number" wire:model="mileage_in" placeholder="Current mileage"
                            class="input input-bordered w-full" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Estimated Completion</span></label>
                        <input type="datetime-local" wire:model="estimated_completion"
                            class="input input-bordered w-full" />
                    </div>
                    <div class="form-control sm:col-span-2">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" wire:model="is_combo" class="checkbox checkbox-primary" />
                            <div>
                                <span class="label-text font-medium">Combo (Service + Wash)</span>
                                <p class="text-xs text-base-content/60">Auto-queue for wash when ready</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-between mt-6">
            <button type="button" wire:click="previousStep" class="btn btn-ghost">← Back</button>
            <button type="button" wire:click="nextStep" class="btn btn-primary">Next: Add Items →</button>
        </div>
    @endif

    {{-- STEP 3: Items --}}
    @if ($currentStep === 3)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Job Items</h2>
                    <select wire:model="selectedTemplate" wire:change="applyTemplate"
                        class="select select-bordered select-sm">
                        <option value="">Apply Template...</option>
                        @foreach ($this->templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($items)
                    <div class="space-y-4">
                        @foreach ($items as $index => $item)
                            <div class="border border-base-300 rounded-lg p-4 relative">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="btn btn-ghost btn-xs text-error absolute top-2 right-2">✕</button>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                    <div class="form-control">
                                        <label class="label py-1"><span class="label-text text-xs">Type</span></label>
                                        <select wire:model="items.{{ $index }}.item_type"
                                            class="select select-bordered select-sm">
                                            <option value="labor">Labor</option>
                                            <option value="part">Part</option>
                                        </select>
                                    </div>
                                    <div class="form-control sm:col-span-2">
                                        <label class="label py-1"><span class="label-text text-xs">Description
                                                *</span></label>
                                        <input type="text" wire:model="items.{{ $index }}.description"
                                            placeholder="Description" class="input input-bordered input-sm" />
                                        @error("items.$index.description")
                                            <span class="text-error text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-control">
                                        <label class="label py-1"><span class="label-text text-xs">Qty
                                                *</span></label>
                                        <input type="number" wire:model="items.{{ $index }}.quantity"
                                            step="0.01" min="0" class="input input-bordered input-sm" />
                                    </div>
                                </div>

                                @if (($item['item_type'] ?? '') === 'part')
                                    <div class="form-control mb-3">
                                        <label class="label py-1"><span class="label-text text-xs">Inventory Item
                                                (optional)
                                            </span></label>
                                        <select wire:model="items.{{ $index }}.inventory_item_id"
                                            class="select select-bordered select-sm">
                                            <option value="">None</option>
                                            @foreach ($this->parts as $part)
                                                <option value="{{ $part->id }}">{{ $part->name }}
                                                    ({{ $part->sku }})
                                                    – Stock: {{ $part->quantity }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                @if (!$this->isJobcarder())
                                    <div class="form-control mb-3">
                                        <label class="label py-1"><span class="label-text text-xs">Unit
                                                Price</span></label>
                                        <input type="number" wire:model="items.{{ $index }}.unit_price"
                                            step="1" min="0"
                                            class="input input-bordered input-sm w-40" />
                                        <span class="text-xs text-base-content/50 mt-1">
                                            Total: UGX
                                            {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}
                                        </span>
                                    </div>
                                @else
                                    <p class="text-xs text-base-content/40 italic mb-2">Pricing will be set by the
                                        quoter.</p>
                                @endif

                                <div class="form-control">
                                    <label class="label py-1"><span class="label-text text-xs">Images
                                            (optional)</span></label>
                                    <input type="file" wire:model="itemImages.{{ $index }}" multiple
                                        accept="image/*"
                                        class="file-input file-input-sm file-input-bordered w-full max-w-sm" />
                                    <span wire:loading wire:target="itemImages.{{ $index }}"
                                        class="text-xs text-base-content/50">Uploading...</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-base-content/50 py-8">No items added yet. Add at least one item.</div>
                @endif

                @error('items')
                    <p class="text-error text-sm mt-2">{{ $message }}</p>
                @enderror

                <div class="flex gap-2 mt-4">
                    <button type="button" wire:click="addItem('labor')" class="btn btn-outline btn-sm">+ Add
                        Labor</button>
                    <button type="button" wire:click="addItem('part')" class="btn btn-outline btn-sm">+ Add
                        Part</button>
                </div>

                @if (!$this->isJobcarder() && count($items) > 0)
                    <div class="flex justify-end mt-4 text-sm font-medium">
                        Subtotal: UGX {{ number_format($this->subtotal) }}
                    </div>
                @endif
            </div>
        </div>
        <div class="flex justify-between mt-6">
            <button type="button" wire:click="previousStep" class="btn btn-ghost">← Back</button>
            <button type="button" wire:click="nextStep" class="btn btn-primary">Review & Submit →</button>
        </div>
    @endif

    {{-- STEP 4: Review --}}
    @if ($currentStep === 4)
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Review</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-base-content/50 text-xs uppercase mb-1">Customer</p>
                            <p class="font-medium">
                                {{ optional(\App\Models\Customer::find($customer_id))->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-base-content/50 text-xs uppercase mb-1">Vehicle</p>
                            @php $v = \App\Models\Vehicle::find($vehicle_id); @endphp
                            <p class="font-medium">
                                {{ $v ? $v->registration_number . ' ' . $v->make . ' ' . $v->model : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-base-content/50 text-xs uppercase mb-1">Type / Priority</p>
                            <p class="font-medium capitalize">{{ $type }} / {{ $priority }}</p>
                        </div>
                        @if ($mileage_in)
                            <div>
                                <p class="text-base-content/50 text-xs uppercase mb-1">Mileage In</p>
                                <p class="font-medium">{{ number_format($mileage_in) }} km</p>
                            </div>
                        @endif
                        @if ($customer_notes)
                            <div class="sm:col-span-2">
                                <p class="text-base-content/50 text-xs uppercase mb-1">Customer Notes</p>
                                <p>{{ $customer_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-base mb-3">Items ({{ count($items) }})</h2>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Qty</th>
                                @if (!$this->isJobcarder())
                                    <th class="text-right">Total</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td><span class="badge badge-sm">{{ $item['item_type'] }}</span></td>
                                    <td>{{ $item['description'] }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    @if (!$this->isJobcarder())
                                        <td class="text-right">UGX
                                            {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        @if (!$this->isJobcarder())
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right font-medium">Subtotal:</td>
                                    <td class="text-right font-bold">UGX {{ number_format($this->subtotal) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="flex justify-between mt-4">
                <button type="button" wire:click="previousStep" class="btn btn-ghost">← Back</button>
                <div class="flex flex-col items-end gap-2">
                    @error('closure')
                        <div class="alert alert-error py-2 px-4 text-sm">{{ $message }}</div>
                    @enderror
                    <button type="button" wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Create Work Order</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
