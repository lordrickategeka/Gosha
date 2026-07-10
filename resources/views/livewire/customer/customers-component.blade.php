<div>
<x-layouts.dash-layout title="Customers">
    <h2 class="text-2xl font-bold mb-6">Customer List</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Customer Table -->
    <div class="bg-white rounded shadow-md overflow-x-auto">
        <table class="min-w-full table-auto text-xs">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">#</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Vehicles</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($customers as $customer)
                    @php $rowNumber = $loop->iteration; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-2 py-2">
                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">
                                {{ $rowNumber }}
                            </span>
                        </td>
                        <td class="px-2 py-2">
                            <div class="font-semibold text-gray-900 text-xs">{{ $customer->name }}</div>
                            <div class="text-[10px] text-gray-500">ID: #CU{{ str_pad($customer->id, 3, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td class="px-2 py-2">{{ $customer->phone }}</td>
                        <td class="px-2 py-2">{{ $customer->email }}</td>
                        <td class="px-2 py-2">
                            @if($customer->vehicles && count($customer->vehicles))
                                <div class="flex flex-wrap gap-1 mb-1">
                                    @foreach($customer->vehicles as $vehicle)
                                        <span class="inline-block px-2 py-0.5 text-xs rounded-full font-medium"
                                              style="background-color: {{ $vehicle->color ?? '#e0e7ef' }}; color: #222;">
                                            {{ $vehicle->vehicle_name }} ({{ $vehicle->number_plate }})
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="inline-block px-3 py-1 text-xs bg-primary/10 text-primary rounded-full">0</span>
                            @endif
                            @if($customer->vehicleItems && count($customer->vehicleItems))
                                <div class="mt-1">
                                    <span class="block text-[10px] text-gray-500 font-semibold">Vehicle Items:</span>
                                    <ul class="list-disc ml-4 text-[11px] text-gray-700">
                                        @foreach($customer->vehicleItems as $item)
                                            <li>
                                                {{ $item->item_name }} (Qty: {{ $item->quantity }})
                                                @if($item->part_number)
                                                    - Part #: {{ $item->part_number }}
                                                @endif
                                                @if($item->description)
                                                    - {{ $item->description }}
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($customer->is_active ?? true)
                                <span class="inline-block px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="inline-block px-3 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 space-x-2 text-gray-600">
                            <button wire:click="edit({{ $customer->id }})" class="hover:text-primary" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3zm0 0v3h3" /></svg>
                            </button>
                            <button wire:click="confirmDelete({{ $customer->id }})" class="hover:text-red-600" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <!-- Pagination and rows per page (static example, replace with Livewire pagination if needed) -->
        <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex items-center justify-between">
                <div>
                    <label for="rows" class="mr-2 text-sm text-gray-700">Rows per page:</label>
                    <select id="rows" class="border-gray-300 rounded p-1 text-sm">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
                <div class="ml-4 text-sm text-gray-700">Showing 1-{{ count($customers) }} of {{ count($customers) }} customers</div>
            </div>
            <div>
                <nav class="inline-flex -space-x-px" aria-label="Pagination">
                    <a href="#" class="px-2 py-1 rounded-l border border-gray-300 bg-white text-sm text-gray-500 hover:bg-gray-50">Previous</a>
                    <a href="#" class="px-2 py-1 border-t border-b border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">1</a>
                    <a href="#" class="px-2 py-1 rounded-r border border-gray-300 bg-white text-sm text-gray-500 hover:bg-gray-50">Next</a>
                </nav>
            </div>
        </div>
    </div>
</x-layouts.dash-layout>
</div>
