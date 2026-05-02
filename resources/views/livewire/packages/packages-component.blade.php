<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Wash Packages</h1>
            <p class="text-base-content/60">Manage your wash service packages</p>
        </div>
        <button wire:click="create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Package
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search packages..." class="input input-bordered w-full pl-9 input-sm" />
                    </div>
                </div>
                <select wire:model.live="filterType" class="select select-bordered select-sm">
                    <option value="">All Types</option>
                    <option value="basic">Basic</option>
                    <option value="full">Full</option>
                    <option value="premium">Premium</option>
                    <option value="interior">Interior</option>
                    <option value="exterior">Exterior</option>
                    <option value="engine">Engine</option>
                    <option value="detailing">Detailing</option>
                </select>
                <select wire:model.live="filterStatus" class="select select-bordered select-sm">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Packages Grid -->
    @if($packages->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($packages as $package)
                <div class="card bg-base-100 shadow-sm border border-base-300 {{ !$package->is_active ? 'opacity-60' : '' }}">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-base">{{ $package->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="badge badge-sm badge-outline capitalize">{{ $package->wash_type }}</span>
                                    @if($package->is_active)
                                        <span class="badge badge-sm badge-success">Active</span>
                                    @else
                                        <span class="badge badge-sm badge-ghost">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-primary">{{ number_format($package->price, 2) }}</p>
                                <p class="text-xs text-base-content/60">{{ $package->duration_display }}</p>
                            </div>
                        </div>

                        @if($package->description)
                            <p class="text-sm text-base-content/70 mt-2">{{ Str::limit($package->description, 80) }}</p>
                        @endif

                        @if($package->includes_list)
                            <ul class="mt-2 space-y-1">
                                @foreach(array_slice($package->includes_list, 0, 4) as $item)
                                    <li class="flex items-center gap-1 text-xs text-base-content/70">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-success shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $item }}
                                    </li>
                                @endforeach
                                @if(count($package->includes_list) > 4)
                                    <li class="text-xs text-base-content/50">+{{ count($package->includes_list) - 4 }} more</li>
                                @endif
                            </ul>
                        @endif

                        <div class="card-actions justify-end mt-4 pt-3 border-t border-base-200">
                            <button wire:click="toggleStatus({{ $package->id }})" class="btn btn-ghost btn-xs" title="{{ $package->is_active ? 'Deactivate' : 'Activate' }}">
                                @if($package->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                            </button>
                            <button wire:click="edit({{ $package->id }})" class="btn btn-ghost btn-xs text-info">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button wire:click="delete({{ $package->id }})"
                                wire:confirm="Delete this package? This cannot be undone."
                                class="btn btn-ghost btn-xs text-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
            <div class="mt-4">
                {{ $packages->links() }}
            </div>
        @endif

    @else
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body items-center text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-base-content/20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <p class="text-lg font-medium text-base-content/50">No packages found</p>
                <p class="text-sm text-base-content/40 mb-4">Create your first wash package to get started</p>
                <button wire:click="create" class="btn btn-primary btn-sm">Create Package</button>
            </div>
        </div>
    @endif

    <!-- Create / Edit Modal -->
    @if($showModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">{{ $editingId ? 'Edit Package' : 'New Wash Package' }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Package Name *</span></label>
                        <input type="text" wire:model="name" placeholder="e.g. Premium Full Wash" class="input input-bordered" />
                        @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <!-- Wash Type -->
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Wash Type *</span></label>
                        <select wire:model="wash_type" class="select select-bordered">
                            <option value="basic">Basic</option>
                            <option value="full">Full</option>
                            <option value="premium">Premium</option>
                            <option value="interior">Interior</option>
                            <option value="exterior">Exterior</option>
                            <option value="engine">Engine</option>
                            <option value="detailing">Detailing</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Price *</span></label>
                        <input type="number" wire:model="price" placeholder="0.00" step="0.01" min="0" class="input input-bordered" />
                        @error('price') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <!-- Duration -->
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Duration (minutes) *</span></label>
                        <input type="number" wire:model="estimated_duration_minutes" placeholder="30" min="5" class="input input-bordered" />
                        @error('estimated_duration_minutes') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sort Order -->
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Sort Order</span></label>
                        <input type="number" wire:model="sort_order" placeholder="0" min="0" class="input input-bordered" />
                    </div>

                    <!-- Description -->
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Description</span></label>
                        <textarea wire:model="description" rows="2" placeholder="Brief description of the package..." class="textarea textarea-bordered"></textarea>
                    </div>

                    <!-- Includes List -->
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">What's Included</span></label>
                        <div class="flex gap-2 mb-2">
                            <input type="text" wire:model="newIncludeItem" wire:keydown.enter.prevent="addIncludeItem"
                                placeholder="e.g. Exterior wash, Interior vacuum..." class="input input-bordered input-sm flex-1" />
                            <button type="button" wire:click="addIncludeItem" class="btn btn-sm btn-outline">Add</button>
                        </div>
                        @if($includes)
                            <ul class="space-y-1">
                                @foreach($includes as $i => $item)
                                    <li class="flex items-center justify-between bg-base-200 rounded px-3 py-1.5">
                                        <span class="text-sm">{{ $item }}</span>
                                        <button type="button" wire:click="removeIncludeItem({{ $i }})" class="btn btn-ghost btn-xs text-error">✕</button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <!-- Active Toggle -->
                    <div class="form-control sm:col-span-2">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" wire:model="is_active" class="checkbox checkbox-primary" />
                            <span class="label-text font-medium">Package is active (visible when creating wash orders)</span>
                        </label>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="closeModal" class="btn btn-ghost">Cancel</button>
                    <button type="button" wire:click="save" class="btn btn-primary">
                        {{ $editingId ? 'Update Package' : 'Create Package' }}
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeModal"></div>
        </div>
    @endif
</div>
