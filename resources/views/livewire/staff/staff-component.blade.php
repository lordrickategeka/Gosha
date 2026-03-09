<div>
    <x-layouts.dash-layout title="Staff">
        <h2 class="text-2xl font-bold mb-6">Staff List</h2>
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
                        wire:click="redirectToCreateStaff"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 focus:outline-none text-sm"
                        title="Add a new staff member">
                        + Add Staff
                    </button>

                    <!-- Filter Dropdown -->
                    <div class="relative" title="Filter vehicles">
                        <button
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 focus:outline-none text-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <!-- Dropdown Menu -->

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

        <div class="bg-white rounded shadow-md overflow-x-auto">
            <table class="min-w-full table-auto text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Staff</th>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Base Salary</th>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($staffList as $staff)
                        @php $rowNumber = $loop->iteration; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-2">
                                <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">
                                    {{ $rowNumber }}
                                </span>
                            </td>
                            <td class="px-2 py-2">
                                <div class="font-semibold text-gray-900 text-xs">{{ $staff->name }}</div>
                                <div class="text-[10px] text-gray-500">ID: #ST{{ str_pad($staff->id, 3, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-2 py-2">{{ $staff->phone }}</td>
                            <td class="px-2 py-2">{{ $staff->email }}</td>
                            <td class="px-2 py-2 capitalize">{{ $staff->role }}</td>
                            <td class="px-2 py-2 font-mono">UGX {{ number_format($staff->base_salary, 2) }}</td>
                            <td class="px-2 py-2">
                                @if ($staff->is_active)
                                    <span class="inline-block px-2 py-1 text-[10px] bg-green-100 text-green-800 rounded-full">Active</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-[10px] bg-yellow-100 text-yellow-800 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 space-x-2 text-gray-600">
                                <button wire:click="edit({{ $staff->id }})" class="hover:text-blue-600" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3zm0 0v3h3" /></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $staff->id }})" class="hover:text-red-600" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-4 text-center text-gray-500">No staff found.</td>
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
                    <div class="ml-4 text-sm text-gray-700">Showing 1-{{ count($staffList) }} of {{ count($staffList) }} staff</div>
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

        <!-- Delete Confirmation Modal -->
        @if ($staffId && !$editMode)
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
                    <h3 class="text-lg font-bold mb-4">Delete Staff</h3>
                    <p class="mb-6">Are you sure you want to delete this staff member?</p>
                    <div class="flex justify-end space-x-2">
                        <button wire:click="delete" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete</button>
                        <button wire:click="resetForm" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                    </div>
                </div>
            </div>
        @endif
    </x-layouts.dash-layout>
</div>
