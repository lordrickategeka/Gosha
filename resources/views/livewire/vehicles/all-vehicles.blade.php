<div>
    <x-layouts.dash-layout title="Vehicles">
        <div class="max-w-7xl p-6 bg-gray-100 rounded-lg shadow">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Registered Vehicles</h2>

            <!-- Toolbar with Search, Add, Filter, Columns, and Import/Export -->
            <div
                class="flex flex-wrap items-center justify-between mb-6 p-4 bg-white border border-gray-300 rounded-lg shadow">
                <!-- Search -->
                <div class="flex items-center w-full max-w-md mb-2 md:mb-0" title="Search for vehicles">
                    <i class="fas fa-search text-gray-500 mr-2"></i>
                    <input type="text" wire:model="search"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                        placeholder="Search vehicles...">
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-2">
                    <!-- Add Vehicle Button -->
                    <button
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 focus:outline-none text-sm"
                        title="Add a new vehicle">
                        + Add Vehicle
                    </button>

                    <!-- Filter Dropdown -->
                    <div class="relative" title="Filter vehicles">
                        <button
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 focus:outline-none text-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <!-- Dropdown Menu -->
                        <div
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10 hidden group-hover:block">
                            <select wire:model="filterCustomer"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">All Customers</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Import/Export Dropdown -->
                    <div class="relative" title="Import or export data">
                        <button
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 focus:outline-none text-sm">
                            <i class="fas fa-file-import"></i> Import/Export
                        </button>
                        <!-- Dropdown Menu -->
                        <div
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10 hidden group-hover:block">
                            <a href="#" wire:click="export"
                                class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                title="Export data">Export</a>
                            <label class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                title="Import data">
                                <input type="file" wire:model="importFile" class="hidden">
                                Import
                            </label>
                        </div>
                    </div>

                    <!-- Show Rows Dropdown -->
                    <div title="Select number of rows to display">
                        <label for="show" class="sr-only">Show</label>
                        <select wire:model="perPage" id="show"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Vehicles Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border px-3 py-2">#</th>
                            <th class="border px-3 py-2">Customer</th>
                            <th class="border px-3 py-2">Vehicle Name</th>
                            <th class="border px-3 py-2">Number Plate</th>
                            <th class="border px-3 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            <tr class="hover:bg-gray-100">
                                <td class="border px-3 py-2 text-center">{{ $loop->iteration }}</td>
                                <td class="border px-3 py-2">{{ $vehicle->customer->customer_name ?? 'N/A' }}</td>
                                <td class="border px-3 py-2">{{ $vehicle->vehicle_name }}</td>
                                <td class="border px-3 py-2">{{ $vehicle->number_plate }}</td>
                                <td class="border px-3 py-2 text-center">
                                    @php
                                        $viewUrl = route('vehicles.show', ['vehicleId' => $vehicle->id]);
                                        logger('Generated View URL: ' . $viewUrl);
                                    @endphp
                                    <a href="{{ $viewUrl }}" class="text-blue-500 hover:underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No vehicles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-layouts.dash-layout>
</div>
