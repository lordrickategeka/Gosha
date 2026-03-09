<div>
    <x-layouts.dash-layout title="Create Job Card">
        <div class="max-w-3xl p-3 bg-white rounded-lg shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Job Card</h2>

            @if (session()->has('message'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('message') }}
                </div>
            @endif
            @if ($formError)
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    {{ $formError }}
                </div>
            @endif

            <form wire:submit.prevent="submit" class="space-y-4">                <!-- Progress Bar with Icons -->
                <div class="flex justify-between items-center mb-6"> <!-- Increased margin-bottom for spacing -->
                    <div class="flex items-center space-x-4"> <!-- Added spacing between icons -->
                        <div class="flex items-center">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700">Customer</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full @if($step >= 2) bg-blue-600 text-white @else bg-gray-200 text-gray-500 @endif">
                                <i class="fas fa-car"></i>
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700">Vehicle</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
                        <div class="flex items-center">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full @if($step === 3) bg-blue-600 text-white @else bg-gray-200 text-gray-500 @endif">
                                <i class="fas fa-tools"></i>
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700">Service</span>
                        </div>
                    </div>
                </div>

                @if($step === 1)
                <!-- Step 1: Customer Information -->
                <div class="border border-gray-200 rounded p-3">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Customer Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <!-- Customer Name with Autocomplete -->
                        <div class="relative">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Customer Name *</label>
                            <input type="text" wire:model.debounce.500ms="customer_name" wire:keydown="searchCustomers" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter customer name" required />
                            @if($showCustomerSuggestions && count($customerSuggestions) > 0)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                    @foreach($customerSuggestions as $customer)
                                        <div wire:click="selectCustomer({{ $customer['id'] }})" class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                            <div class="font-medium">{{ $customer['customer_name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer['phone'] }}</div>
                                            @if(!empty($customer['email']))
                                                <div class="text-sm text-gray-500">{{ $customer['email'] }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number *</label>
                            <input type="tel" wire:model.defer="phone" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter phone number" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" wire:model.defer="email" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter email address" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Company Name</label>
                            <input type="text" wire:model.defer="company_name" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter company name" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Contact Person</label>
                            <input type="text" wire:model.defer="contact_person" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter contact person" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                            <textarea wire:model.defer="address" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter address" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                @endif

                @if($step === 2)
                <!-- Step 2: Vehicle Information -->
                <div class="border border-gray-200 rounded p-3">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Vehicle Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="relative">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Vehicle Name *</label>
                            <input type="text" wire:model.debounce.500ms="vehicle_name" wire:keydown="searchVehicles" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter vehicle name" required @if($addingNewVehicle) disabled @endif />
                            @if($showVehicleSuggestions && count($vehicleSuggestions) > 0)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                    @foreach($vehicleSuggestions as $vehicle)
                                        <div wire:click="selectVehicle('{{ $vehicle['id'] }}')" class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                            <div class="font-medium">{{ $vehicle['vehicle_name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $vehicle['number_plate'] }}</div>
                                            @if(!empty($vehicle['customer_name']))
                                                <div class="text-sm text-gray-500">Owner: {{ $vehicle['customer_name'] }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @if($addingNewVehicle)
                            <div class="col-span-2 bg-yellow-50 border border-yellow-200 rounded p-2 mt-2">
                            <div class="text-xs font-semibold text-yellow-800 mb-1">Add New Vehicle</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Vehicle Name *</label>
                                    <input type="text" wire:model="vehicle_name" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Enter vehicle name" required />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Number Plate *</label>
                                    <input type="text" wire:model="number_plate" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Enter number plate" required />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Vehicle Type *</label>
                                    <select wire:model="vehicle_type_id" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                        <option value="">Select vehicle type</option>
                                        @foreach($vehicleTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Color</label>
                                    <input type="text" wire:model="color" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Enter vehicle color" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Chassis Number</label>
                                    <input type="text" wire:model="chasis_number" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Enter chassis number" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">VIN Number</label>
                                    <input type="text" wire:model="vin_number" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Enter VIN number" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Mileage</label>
                                    <input type="number" min="0" wire:model="mileage" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Enter mileage" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Fuel Type</label>
                                    <input type="text" wire:model="fuel_type" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="e.g. Petrol, Diesel, Electric" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Fuel Level</label>
                                    <input type="text" wire:model="fuel_level" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="e.g. Full, 3/4, 50%" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Physical Condition</label>
                                    <input type="text" wire:model="physical_condition" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Brief physical condition" />
                                </div>
                            </div>
                        </div>
                        @endif
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Number Plate *</label>
                            <input type="text" wire:model.defer="number_plate" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter number plate" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Vehicle Type *</label>
                            <select wire:model.defer="vehicle_type_id" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" required>
                                <option value="">Select vehicle type</option>
                                @foreach($vehicleTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Color</label>
                            <input type="text" wire:model.defer="color" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter vehicle color" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Chassis Number</label>
                            <input type="text" wire:model.defer="chasis_number" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter chassis number" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">VIN Number</label>
                            <input type="text" wire:model.defer="vin_number" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter VIN number" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mileage</label>
                            <input type="number" min="0" wire:model.defer="mileage" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter mileage" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Fuel Type</label>
                            <input type="text" wire:model.defer="fuel_type" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="e.g. Petrol, Diesel, Electric" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Fuel Level</label>
                            <input type="text" wire:model.defer="fuel_level" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="e.g. Full, 3/4, 50%" />
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Physical Condition</label>
                            <input type="text" wire:model.defer="physical_condition" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Brief physical condition" />
                        </div>
                    </div>

                    <!-- Items Left on Vehicle -->
                    <div class="mt-4">
                        <h4 class="text-xs font-semibold text-gray-700 mb-2">Items Left on Vehicle</h4>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-2 mb-2">
                            <input type="text" wire:model.defer="item_name" class="px-2 py-1 border border-gray-300 rounded text-xs" placeholder="Item Name*" />
                            <input type="text" wire:model.defer="item_description" class="px-2 py-1 border border-gray-300 rounded text-xs" placeholder="Description" />
                            <input type="number" min="1" wire:model.defer="item_quantity" class="px-2 py-1 border border-gray-300 rounded text-xs" placeholder="Qty*" />
                            <input type="text" wire:model.defer="item_part_number" class="px-2 py-1 border border-gray-300 rounded text-xs" placeholder="Part Number" />
                            <button type="button" wire:click="addVehicleItem" class="px-2 py-1 bg-gray-900 text-white rounded text-xs hover:bg-gray-800">Add Item</button>
                        </div>

                        @if (session()->has('error'))
                            <div class="mb-2 p-2 bg-red-100 text-red-800 rounded">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if(count($vehicle_items) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto text-xs border rounded">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-2 py-1 border">#</th>
                                        <th class="px-2 py-1 border">Item Name</th>
                                        <th class="px-2 py-1 border">Description</th>
                                        <th class="px-2 py-1 border">Qty</th>
                                        <th class="px-2 py-1 border">Part #</th>
                                        <th class="px-2 py-1 border"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicle_items as $idx => $item)
                                    <tr>
                                        <td class="px-2 py-1 border">{{ $idx+1 }}</td>
                                        <td class="px-2 py-1 border">{{ $item['item_name'] }}</td>
                                        <td class="px-2 py-1 border">{{ $item['description'] ?? '' }}</td>
                                        <td class="px-2 py-1 border text-center">{{ $item['quantity'] }}</td>
                                        <td class="px-2 py-1 border">{{ $item['part_number'] }}</td>
                                        <td class="px-2 py-1 border text-center">
                                            <button type="button" wire:click="removeVehicleItem({{ $idx }})" class="text-red-500 hover:underline">Remove</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($step === 3)
                <!-- Step 3: Service Details -->
                <div class="border border-gray-200 rounded p-3">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Service Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Service Description *</label>
                            <textarea wire:model.defer="service_description" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter service description" rows="3" required></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Estimated Cost *</label>
                            <input type="number" wire:model.defer="estimated_cost" class="w-full px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Enter estimated cost" required />
                        </div>
                    </div>
                </div>
                @endif

                <!-- Step Navigation Buttons -->
                <div class="flex justify-between space-x-2 mt-4">
                    <div>
                        @if($step > 1)
                            <button type="button" wire:click="prevStep" class="px-4 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-xs">Previous</button>
                        @endif
                    </div>
                    <div class="flex space-x-4">
                        <button type="button" wire:click="resetForm" class="px-4 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-xs">Cancel</button>
                        @if($step < 3)
                            <button type="button" wire:click="nextStep" class="px-4 py-1 bg-gray-900 text-white rounded hover:bg-gray-800 text-xs">Next</button>
                        @else
                            <button type="submit" wire:loading.attr="disabled" class="px-4 py-1 bg-gray-900 text-white rounded hover:bg-gray-800 disabled:opacity-50 text-xs">
                                <span wire:loading.remove> Create Job Card </span>
                                <span wire:loading> Creating... </span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </x-layouts.dash-layout>
</div>
