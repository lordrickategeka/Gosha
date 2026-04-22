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

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer & Vehicle -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Customer & Vehicle</h2>

                        <!-- Customer Search -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Customer *</span>
                                <button type="button" wire:click="$set('showNewCustomerForm', true)" class="btn btn-ghost btn-xs">
                                    + New Customer
                                </button>
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="customerSearch"
                                    wire:focus="$set('showCustomerDropdown', true)"
                                    placeholder="Search by name or phone..."
                                    class="input input-bordered w-full"
                                    autocomplete="off"
                                />

                                @if($showCustomerDropdown && $this->customers->count() > 0)
                                    <ul class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                        @foreach($this->customers as $customer)
                                            <li>
                                                <button
                                                    type="button"
                                                    wire:click="selectCustomer({{ $customer->id }})"
                                                    class="w-full px-4 py-2 text-left hover:bg-base-200"
                                                >
                                                    <div class="font-medium">{{ $customer->name }}</div>
                                                    <div class="text-sm text-base-content/60">{{ $customer->phone }}</div>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            @error('customer_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>

                        <!-- New Customer Form -->
                        @if($showNewCustomerForm)
                            <div class="bg-base-200 p-4 rounded-lg mb-4">
                                <h3 class="font-medium mb-3">New Customer</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <input type="text" wire:model="newCustomerName" placeholder="Name *" class="input input-bordered input-sm" />
                                    <input type="text" wire:model="newCustomerPhone" placeholder="Phone *" class="input input-bordered input-sm" />
                                    <input type="email" wire:model="newCustomerEmail" placeholder="Email" class="input input-bordered input-sm" />
                                </div>
                                <div class="flex gap-2 mt-3">
                                    <button type="button" wire:click="createNewCustomer" class="btn btn-primary btn-sm">Save Customer</button>
                                    <button type="button" wire:click="$set('showNewCustomerForm', false)" class="btn btn-ghost btn-sm">Cancel</button>
                                </div>
                            </div>
                        @endif

                        <!-- Vehicle Selection -->
                        @if($customer_id)
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Vehicle *</span>
                                    <button type="button" wire:click="$set('showNewVehicleForm', true)" class="btn btn-ghost btn-xs">
                                        + New Vehicle
                                    </button>
                                </label>
                                <select wire:model="vehicle_id" class="select select-bordered w-full">
                                    <option value="">Select vehicle...</option>
                                    @foreach($this->vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">
                                            {{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <!-- New Vehicle Form -->
                            @if($showNewVehicleForm)
                                <div class="bg-base-200 p-4 rounded-lg mt-4">
                                    <h3 class="font-medium mb-3">New Vehicle</h3>
                                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                        <input type="text" wire:model="newVehicleRegNumber" placeholder="Reg Number *" class="input input-bordered input-sm" />
                                        <input type="text" wire:model="newVehicleMake" placeholder="Make" class="input input-bordered input-sm" />
                                        <input type="text" wire:model="newVehicleModel" placeholder="Model" class="input input-bordered input-sm" />
                                        <input type="number" wire:model="newVehicleYear" placeholder="Year" class="input input-bordered input-sm" />
                                        <input type="text" wire:model="newVehicleColor" placeholder="Color" class="input input-bordered input-sm" />
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <button type="button" wire:click="createNewVehicle" class="btn btn-primary btn-sm">Save Vehicle</button>
                                        <button type="button" wire:click="$set('showNewVehicleForm', false)" class="btn btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Customer Notes</h2>
                        <textarea
                            wire:model="customer_notes"
                            rows="3"
                            placeholder="What did the customer report? Any specific issues or requests..."
                            class="textarea textarea-bordered w-full"
                        ></textarea>
                    </div>
                </div>

                <!-- Job Items -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="card-title text-lg">Job Items</h2>
                            <div class="flex gap-2">
                                <select wire:model="selectedTemplate" wire:change="applyTemplate" class="select select-bordered select-sm">
                                    <option value="">Apply Template...</option>
                                    @foreach($this->templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th class="w-24">Type</th>
                                        <th>Description</th>
                                        <th class="w-24">Qty</th>
                                        <th class="w-32">Unit Price</th>
                                        <th class="w-32">Total</th>
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $index => $item)
                                        <tr>
                                            <td>
                                                <select wire:model="items.{{ $index }}.item_type" class="select select-bordered select-xs w-full">
                                                    <option value="labor">Labor</option>
                                                    <option value="part">Part</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    wire:model="items.{{ $index }}.description"
                                                    placeholder="Description"
                                                    class="input input-bordered input-sm w-full"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    wire:model="items.{{ $index }}.quantity"
                                                    step="0.01"
                                                    min="0"
                                                    class="input input-bordered input-sm w-full"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    wire:model="items.{{ $index }}.unit_price"
                                                    step="1"
                                                    min="0"
                                                    class="input input-bordered input-sm w-full"
                                                />
                                            </td>
                                            <td class="text-right font-medium">
                                                UGX {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}
                                            </td>
                                            <td>
                                                <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-ghost btn-xs text-error">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-base-content/50 py-4">
                                                No items added yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right font-medium">Subtotal:</td>
                                        <td class="text-right font-bold">UGX {{ number_format($this->subtotal) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="flex gap-2 mt-4">
                            <button type="button" wire:click="addItem('labor')" class="btn btn-outline btn-sm">
                                + Add Labor
                            </button>
                            <button type="button" wire:click="addItem('part')" class="btn btn-outline btn-sm">
                                + Add Part
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Job Details -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Job Details</h2>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Job Type</span>
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
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Priority</span>
                            </label>
                            <select wire:model="priority" class="select select-bordered w-full">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Assign Bay</span>
                            </label>
                            <select wire:model="service_bay_id" class="select select-bordered w-full">
                                <option value="">Select later...</option>
                                @foreach($this->serviceBays as $bay)
                                    <option value="{{ $bay->id }}">{{ $bay->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Assign Technician</span>
                            </label>
                            <select wire:model="assigned_technician_id" class="select select-bordered w-full">
                                <option value="">Assign later...</option>
                                @foreach($this->technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Mileage In</span>
                            </label>
                            <input
                                type="number"
                                wire:model="mileage_in"
                                placeholder="Current mileage"
                                class="input input-bordered w-full"
                            />
                        </div>

                        <div class="form-control">
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

                <!-- Summary -->
                <div class="card bg-primary text-primary-content shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Summary</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Items:</span>
                                <span>{{ count($items) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span class="font-bold">UGX {{ number_format($this->subtotal) }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-accent w-full" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">Create Work Order</span>
                                <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
