<div>
    <x-layouts.dash-layout title="Service Types">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-black">Service Types</h2>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle w-5 h-5 text-black"></i>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        <div class="mb-8">
            <form wire:submit.prevent="{{ $editMode ? 'update' : 'create' }}" class="card bg-base-100 shadow-md rounded-lg border border-gray-200 p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="name" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        @error('name')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" wire:model.defer="price" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        @error('price')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Estimated Duration (min) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model.defer="estimated_duration" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        @error('estimated_duration')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div class="flex items-center space-x-2 mt-6">
                        <input type="checkbox" wire:model.defer="is_active" id="is_active" class="checkbox checkbox-primary">
                        <label for="is_active" class="text-sm text-gray-700">Active</label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model.defer="description" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" rows="3"></textarea>
                    @error('description')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save w-5 h-5 mr-2 text-white"></i>
                        {{ $editMode ? 'Update' : 'Add' }} Service Type
                    </button>
                    @if ($editMode)
                        <button type="button" wire:click="resetForm" class="btn btn-outline">Cancel</button>
                    @endif
                </div>
            </form>
        </div>

        <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full table-auto text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Service</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Price</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Duration</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($serviceTypes as $type)
                        @php $rowNumber = $loop->iteration; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-200 text-black font-bold text-xs">
                                    {{ $rowNumber }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-black text-xs">{{ $type->name }}</div>
                                <div class="text-[10px] text-gray-500">ID: #S{{ str_pad($type->id, 3, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-4 py-3 max-w-xs truncate text-gray-700">{{ $type->description }}</td>
                            <td class="px-4 py-3 font-mono text-black">UGX {{ number_format($type->price, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $type->estimated_duration }} min</td>
                            <td class="px-4 py-3">
                                @if ($type->is_active)
                                    <span class="badge badge-success badge-sm">Active</span>
                                @else
                                    <span class="badge badge-warning badge-sm">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 space-x-2">
                                <button wire:click="edit({{ $type->id }})" class="btn btn-ghost btn-xs text-primary hover:text-primary-dark" title="Edit">
                                    <i class="fas fa-edit w-4 h-4 text-black"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $type->id }})" class="btn btn-ghost btn-xs text-red-500 hover:text-red-700" title="Delete">
                                    <i class="fas fa-trash w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                                <p>No service types found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <label for="rows" class="text-sm text-gray-700">Rows per page:</label>
                    <select id="rows" class="select select-bordered select-sm">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
                <div class="text-sm text-gray-700">Showing 1-{{ count($serviceTypes) }} of {{ count($serviceTypes) }}</div>
            </div>
        </div>

        <!-- Delete Confirmation Modal using new modal components -->
        <x-ui-modal :show="$serviceTypeId && !$editMode" closeMethod="resetForm" maxWidth="sm">
            <x-modal-header closeMethod="resetForm">
                Delete Service Type
            </x-modal-header>
            
            <x-modal-body>
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle w-6 h-6 text-red-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-700">Are you sure you want to delete this service type? This action cannot be undone.</p>
                    </div>
                </div>
            </x-modal-body>
            
            <x-modal-footer>
                <button wire:click="delete" class="btn btn-error text-white">
                    <i class="fas fa-trash w-4 h-4 mr-2"></i>
                    Delete
                </button>
                <button wire:click="resetForm" class="btn btn-outline">Cancel</button>
            </x-modal-footer>
        </x-ui-modal>
    </x-layouts.dash-layout>
</div>
