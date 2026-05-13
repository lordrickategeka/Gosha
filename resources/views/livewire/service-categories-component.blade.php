<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-black">Service Categories</h2>
        <button wire:click="openCreateForm" class="btn btn-primary">
            <i class="fas fa-plus w-4 h-4 mr-2 text-white"></i>
            New Category
        </button>
    </div>

    <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full table-auto text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($serviceCategories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-200 text-black font-bold text-xs">
                                    {{ $loop->iteration }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-black text-xs">{{ $category->name }}</div>
                                <div class="text-[10px] text-gray-500">ID: #SC{{ str_pad($category->id, 3, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-4 py-3 max-w-xs truncate text-gray-700">{{ $category->description ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($category->is_active)
                                    <span class="badge badge-success badge-sm">Active</span>
                                @else
                                    <span class="badge badge-warning badge-sm">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 8c1.1 0 2-1 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44 border border-base-300">
                                        <li>
                                            <a wire:click="edit({{ $category->id }})">
                                                <i class="fas fa-edit w-4 h-4"></i>
                                                Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a wire:click="confirmDelete({{ $category->id }})" class="text-red-500">
                                                <i class="fas fa-trash w-4 h-4"></i>
                                                Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                                <p>No service categories found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    <x-ui-modal :show="$showFormModal" closeMethod="resetForm" maxWidth="2xl">
        <x-modal-header closeMethod="resetForm">
            {{ $editMode ? 'Edit Service Category' : 'Create Service Category' }}
        </x-modal-header>

        <form wire:submit.prevent="{{ $editMode ? 'update' : 'create' }}">
            <x-modal-body>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="name" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                        @error('name')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="flex items-center space-x-2 mt-6">
                        <input type="checkbox" wire:model.defer="is_active" id="category_is_active" class="checkbox checkbox-primary">
                        <label for="category_is_active" class="text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model.defer="description" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" rows="3"></textarea>
                    @error('description')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                </div>
            </x-modal-body>

            <x-modal-footer>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save w-4 h-4 mr-2 text-white"></i>
                    {{ $editMode ? 'Update' : 'Create' }} Category
                </button>
                <button type="button" wire:click="resetForm" class="btn btn-outline">Cancel</button>
            </x-modal-footer>
        </form>
    </x-ui-modal>

    <x-ui-modal :show="$serviceCategoryId && !$editMode" closeMethod="resetForm" maxWidth="sm">
        <x-modal-header closeMethod="resetForm">
            Delete Service Category
        </x-modal-header>

        <x-modal-body>
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle w-6 h-6 text-red-500"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-700">Are you sure you want to delete this service category? This action cannot be undone.</p>
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
</div>
