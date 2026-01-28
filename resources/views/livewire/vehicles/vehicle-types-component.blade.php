<div>
    <x-layouts.dash-layout title="Vehicle-Types">
    <h2 class="text-2xl font-bold mb-6">Vehicle Types</h2>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-8">
        <form wire:submit.prevent="{{ $editMode ? 'update' : 'create' }}"
            class="bg-white p-6 rounded shadow-md space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="name"
                        class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-gray-200" />
                    @error('name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-gray-700">Base Price <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" wire:model.defer="base_price"
                        class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-gray-200" />
                    @error('base_price')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div>
                <label class="block text-gray-700">Description</label>
                <textarea wire:model.defer="description" class="w-full border-gray-300 rounded p-2 focus:ring focus:ring-gray-200"></textarea>
                @error('description')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" wire:model.defer="is_active" id="is_active" class="rounded border-gray-300">
                <label for="is_active" class="text-gray-700">Active</label>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    {{ $editMode ? 'Update' : 'Add' }} Vehicle Type
                </button>
                @if ($editMode)
                    <button type="button" wire:click="resetForm"
                        class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded shadow-md overflow-x-auto">
        <table class="min-w-full table-auto text-xs">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">#</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Base Price</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($vehicleTypes as $type)
                    @php $rowNumber = $loop->iteration; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-2 py-2">
                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">
                                {{ $rowNumber }}
                            </span>
                        </td>
                        <td class="px-2 py-2 font-semibold text-gray-900">{{ $type->name }}</td>
                        <td class="px-2 py-2">{{ $type->description }}</td>
                        <td class="px-2 py-2 font-mono">UGX {{ number_format($type->base_price, 2) }}</td>
                        <td class="px-2 py-2">
                            @if ($type->is_active)
                                <span class="inline-block px-2 py-1 text-[10px] bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="inline-block px-2 py-1 text-[10px] bg-yellow-100 text-yellow-800 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-2 py-2 space-x-2 text-gray-600">
                            <button wire:click="edit({{ $type->id }})" class="hover:text-blue-600" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3zm0 0v3h3" /></svg>
                            </button>
                            <button wire:click="confirmDelete({{ $type->id }})" class="hover:text-red-600" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-2 py-2 text-center text-gray-500">No vehicle types found.</td>
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
                <div class="ml-4 text-sm text-gray-700">Showing 1-{{ count($vehicleTypes) }} of {{ count($vehicleTypes) }} vehicle types</div>
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
    @if ($vehicleTypeId && !$editMode)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
            <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
                <h3 class="text-lg font-bold mb-4">Delete Vehicle Type</h3>
                <p class="mb-6">Are you sure you want to delete this vehicle type?</p>
                <div class="flex justify-end space-x-2">
                    <button wire:click="delete"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete</button>
                    <button wire:click="resetForm"
                        class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif
    </x-layouts.dash-layout>
</div>
