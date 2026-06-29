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

<form wire:submit="save" class="max-w-4xl">
        <!-- Owner -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Owner</h2>
            </div>
            <div class="p-6">
                @if($customer_id)
                    <!-- Read-only when owner is already auto-filled -->
                    <input type="text" value="{{ $customerSearch }}" class="input input-bordered w-full bg-gray-100" readonly />
                @else
                    <!-- Editable when no owner assigned yet -->
                    <div class="form-control max-w-lg">
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
                    </div>
                @endif
            </div>
        </div>

        <!-- Vehicle Information Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Vehicle Information</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">Registration Number *</td>
                            <td class="px-6 py-3">
                                <input type="text" wire:model="registration_number" class="input input-bordered uppercase" />
                                @error('registration_number') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">Status</td>
                            <td class="px-6 py-3">
                                <select wire:model="status" class="select select-bordered">
                                    <option value="active">Active</option>
                                    <option value="in_shop">In Shop</option>
                                    <option value="decommissioned">Decommissioned</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Make</td>
                            <td class="px-6 py-3">
                                <input type="text" wire:model="make" class="input input-bordered" />
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Model</td>
                            <td class="px-6 py-3">
                                <input type="text" wire:model="model" class="input input-bordered" />
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Year</td>
                            <td class="px-6 py-3">
                                <input type="number" wire:model="year" class="input input-bordered" min="1900" max="2035" />
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Color</td>
                            <td class="px-6 py-3">
                                <input type="text" wire:model="color" class="input input-bordered" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VIN & Engine Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold">VIN & Engine</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">VIN</td>
                            <td class="px-6 py-3">
                                <input type="text" wire:model="vin" class="input input-bordered uppercase" maxlength="17" />
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">Engine Number</td>
                            <td class="px-6 py-3">
                                <input type="text" wire:model="engine_number" class="input input-bordered uppercase" />
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Engine Code</td>
                            <td class="px-6 py-3">
                                <input type="text" wire:model="engine_code" class="input input-bordered uppercase" />
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Displacement (L)</td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.1" wire:model="engine_displacement" class="input input-bordered" min="0" max="10" />
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Fuel Type</td>
                            <td class="px-6 py-3">
                                <select wire:model="fuel_type" class="select select-bordered">
                                    <option value="gasoline">Gasoline</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="electric">Electric</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Mileage</td>
                            <td class="px-6 py-3">
                                <input type="number" wire:model="mileage" class="input input-bordered" min="0" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transmission & Drivetrain Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Transmission & Drivetrain</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">Transmission</td>
                            <td class="px-6 py-3">
                                <select wire:model="transmission_type" class="select select-bordered">
                                    <option value="automatic">Automatic</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">Drivetrain</td>
                            <td class="px-6 py-3">
                                <select wire:model="drivetrain_type" class="select select-bordered">
                                    <option value="">Select</option>
                                    <option value="fwd">FWD</option>
                                    <option value="rwd">RWD</option>
                                    <option value="awd">AWD</option>
                                    <option value="4wd">4WD</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Financial & Lifecycle</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">In-Service Date</td>
                            <td class="px-6 py-3">
                                <input type="date" wire:model="in_service_date" class="input input-bordered" />
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500 w-48">Acquisition Date</td>
                            <td class="px-6 py-3">
                                <input type="date" wire:model="acquisition_date" class="input input-bordered" />
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Acquisition Cost</td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.01" wire:model="acquisition_cost" class="input input-bordered" placeholder="0.00" min="0" />
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Current Value</td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.01" wire:model="current_value" class="input input-bordered" placeholder="0.00" min="0" />
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-sm font-medium text-gray-500">Ownership</td>
                            <td class="px-6 py-3">
                                <select wire:model="ownership_status" class="select select-bordered">
                                    <option value="owned">Owned</option>
                                    <option value="leased">Leased</option>
                                    <option value="financed">Financed</option>
                                    <option value="customer_owned">Customer Owned</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Notes - Always Visible -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Additional Notes</h2>
            </div>
            <div class="p-6">
                <textarea wire:model="notes" rows="4" class="textarea textarea-bordered w-full" placeholder="Any notes about the vehicle..."></textarea>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
