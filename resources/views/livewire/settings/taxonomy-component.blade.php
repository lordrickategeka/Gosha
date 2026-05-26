<div class="space-y-6">
    <div class="card bg-base-100 shadow-sm border border-base-300">
        <div class="card-body">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="app-kicker">System taxonomy</p>
                    <h2 class="text-2xl font-bold tracking-tight">Taxonomy Settings</h2>
                    <p class="text-base-content/60">Manage inventory and service classification from one place.</p>
                </div>

                @if (session()->has('message'))
                    <div class="alert alert-success lg:max-w-md">
                        <span>{{ session('message') }}</span>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                <button type="button" wire:click="setSection('inventory_categories')" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ $section === 'inventory_categories' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-100 text-base-content hover:bg-base-200/70' }}">
                    <span>Inventory Categories</span>
                    <span class="badge badge-sm {{ $section === 'inventory_categories' ? 'badge-primary' : 'badge-ghost' }}">{{ $inventoryCategories->total() }}</span>
                </button>
                <button type="button" wire:click="setSection('inventory_types')" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ $section === 'inventory_types' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-100 text-base-content hover:bg-base-200/70' }}">
                    <span>Inventory Types</span>
                    <span class="badge badge-sm {{ $section === 'inventory_types' ? 'badge-primary' : 'badge-ghost' }}">{{ $inventoryTypes->count() }}</span>
                </button>
                <button type="button" wire:click="setSection('service_types')" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ $section === 'service_types' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-100 text-base-content hover:bg-base-200/70' }}">
                    <span>Service Types</span>
                    <span class="badge badge-sm {{ $section === 'service_types' ? 'badge-primary' : 'badge-ghost' }}">{{ $serviceTypes->count() }}</span>
                </button>
                <button type="button" wire:click="setSection('service_categories')" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ $section === 'service_categories' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-base-300 bg-base-100 text-base-content hover:bg-base-200/70' }}">
                    <span>Service Categories</span>
                    <span class="badge badge-sm {{ $section === 'service_categories' ? 'badge-primary' : 'badge-ghost' }}">{{ $serviceCategories->count() }}</span>
                </button>
            </div>
        </div>
    </div>

    @if($section === 'inventory_categories')
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body space-y-4">
                    <div>
                        <h3 class="card-title text-lg">{{ $inventoryCategoryId ? 'Edit Inventory Category' : 'New Inventory Category' }}</h3>
                        <p class="text-sm text-base-content/60">Categories are scoped to your vendor and can auto-generate a code if left blank.</p>
                    </div>

                    <form wire:submit.prevent="saveInventoryCategory" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control sm:col-span-2">
                                <label class="block text-sm font-medium mb-2">Name *</label>
                                <input type="text" wire:model.live="inventoryCategoryName" class="input input-bordered" />
                                @error('inventoryCategoryName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Type *</label>
                                <select wire:model.live="inventoryCategoryType" class="select select-bordered">
                                    <option value="service_parts">Service Parts</option>
                                    <option value="wash_supplies">Wash Supplies</option>
                                    <option value="consumables">Consumables</option>
                                    <option value="tools">Tools</option>
                                </select>
                                @error('inventoryCategoryType') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Parent Category</label>
                                <select wire:model="inventoryCategoryParentId" class="select select-bordered">
                                    <option value="">None</option>
                                    @foreach($inventoryCategoryParents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                                @error('inventoryCategoryParentId') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Code</label>
                                <input type="text" wire:model.defer="inventoryCategoryCode" class="input input-bordered" placeholder="Auto-generated if left blank" />
                                <span class="text-xs text-base-content/60 mt-1">If blank, the system will generate a code on save.</span>
                                @error('inventoryCategoryCode') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control sm:col-span-2">
                                <label class="block text-sm font-medium mb-2">Description</label>
                                <textarea wire:model.defer="inventoryCategoryDescription" rows="3" class="textarea textarea-bordered"></textarea>
                                @error('inventoryCategoryDescription') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control sm:col-span-2">
                                <label class="label cursor-pointer justify-start gap-3 px-0">
                                    <input type="checkbox" wire:model="inventoryCategoryIsActive" class="toggle toggle-primary" />
                                    <span class="label-text font-medium">Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if($inventoryCategoryId)
                                <button type="button" wire:click="resetInventoryCategoryForm" class="btn btn-ghost">Cancel</button>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-lg px-5">{{ $inventoryCategoryId ? 'Update' : 'Create' }} Category</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Code</th>
                                    <th>Parent</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventoryCategories as $category)
                                    <tr>
                                        <td>
                                            <div class="font-semibold">{{ $category->name }}</div>
                                        </td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $category->type)) }}</td>
                                        <td><span class="font-mono text-xs">{{ $category->code ?? 'Auto' }}</span></td>
                                        <td>{{ $category->parent?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-warning' }} badge-sm">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-end">
                                                <button tabindex="0" type="button" class="btn btn-ghost btn-sm btn-circle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                                    </svg>
                                                </button>
                                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                    <li>
                                                        <button type="button" wire:click="editInventoryCategory({{ $category->id }})">Edit</button>
                                                    </li>
                                                    <li>
                                                        <button type="button" onclick="if (confirm('Delete this inventory category?')) { $wire.deleteInventoryCategory({{ $category->id }}) }" class="text-error">Delete</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-10 text-base-content/60">No inventory categories found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-3 border-t border-base-300 bg-base-100">
                        {{ $inventoryCategories->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($section === 'inventory_types')
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body space-y-4">
                    <div>
                        <h3 class="card-title text-lg">{{ $inventoryTypeId ? 'Edit Inventory Type' : 'New Inventory Type' }}</h3>
                        <p class="text-sm text-base-content/60">Inventory types live under a chosen inventory category.</p>
                    </div>

                    <form wire:submit.prevent="saveInventoryType" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Name *</label>
                                <input type="text" wire:model.defer="inventoryTypeName" class="input input-bordered" />
                                @error('inventoryTypeName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Inventory Category *</label>
                                <select wire:model="inventoryTypeCategoryId" class="select select-bordered">
                                    <option value="">Select category...</option>
                                    @foreach($inventoryTypeCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} ({{ str_replace('_', ' ', $category->type) }})</option>
                                    @endforeach
                                </select>
                                @error('inventoryTypeCategoryId') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if($inventoryTypeId)
                                <button type="button" wire:click="resetInventoryTypeForm" class="btn btn-ghost">Cancel</button>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-lg px-5">{{ $inventoryTypeId ? 'Update' : 'Create' }} Type</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventoryTypes as $type)
                                    <tr>
                                        <td class="font-semibold">{{ $type->name }}</td>
                                        <td>{{ $type->category?->name ?? '-' }}</td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-end">
                                                <button tabindex="0" type="button" class="btn btn-ghost btn-sm btn-circle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                                    </svg>
                                                </button>
                                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                    <li>
                                                        <button type="button" wire:click="editInventoryType({{ $type->id }})">Edit</button>
                                                    </li>
                                                    <li>
                                                        <button type="button" onclick="if (confirm('Delete this inventory type?')) { $wire.deleteInventoryType({{ $type->id }}) }" class="text-error">Delete</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-10 text-base-content/60">No inventory types found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($section === 'service_types')
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body space-y-4">
                    <div>
                        <h3 class="card-title text-lg">{{ $serviceTypeId ? 'Edit Service Type' : 'New Service Type' }}</h3>
                        <p class="text-sm text-base-content/60">Set the core price and duration used by job cards and other service flows.</p>
                    </div>

                    <form wire:submit.prevent="saveServiceType" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control sm:col-span-2">
                                <label class="block text-sm font-medium mb-2">Name *</label>
                                <input type="text" wire:model.defer="serviceTypeName" class="input input-bordered" />
                                @error('serviceTypeName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Price (UGX) *</label>
                                <input type="number" wire:model.defer="serviceTypePrice" min="0" step="0.01" class="input input-bordered" />
                                @error('serviceTypePrice') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Duration (minutes) *</label>
                                <input type="number" wire:model.defer="serviceTypeEstimatedDuration" min="1" class="input input-bordered" />
                                @error('serviceTypeEstimatedDuration') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if($serviceTypeId)
                                <button type="button" wire:click="resetServiceTypeForm" class="btn btn-ghost">Cancel</button>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-lg px-5">{{ $serviceTypeId ? 'Update' : 'Create' }} Service Type</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($serviceTypes as $type)
                                    <tr>
                                        <td class="font-semibold">{{ $type->name }}</td>
                                        <td>UGX {{ number_format($type->price, 2) }}</td>
                                        <td>{{ $type->estimated_duration }} min</td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-end">
                                                <button tabindex="0" type="button" class="btn btn-ghost btn-sm btn-circle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                                    </svg>
                                                </button>
                                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                    <li>
                                                        <button type="button" wire:click="editServiceType({{ $type->id }})">Edit</button>
                                                    </li>
                                                    <li>
                                                        <button type="button" onclick="if (confirm('Delete this service type?')) { $wire.deleteServiceType({{ $type->id }}) }" class="text-error">Delete</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-10 text-base-content/60">No service types found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($section === 'service_categories')
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body space-y-4">
                    <div>
                        <h3 class="card-title text-lg">{{ $serviceCategoryId ? 'Edit Service Category' : 'New Service Category' }}</h3>
                        <p class="text-sm text-base-content/60">Group services into a clean naming structure for scheduling and reporting.</p>
                    </div>

                    <form wire:submit.prevent="saveServiceCategory" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Name *</label>
                                <input type="text" wire:model.defer="serviceCategoryName" class="input input-bordered" />
                                @error('serviceCategoryName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Description</label>
                                <textarea wire:model.defer="serviceCategoryDescription" rows="3" class="textarea textarea-bordered"></textarea>
                                @error('serviceCategoryDescription') <span class="text-error text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3 px-0">
                                    <input type="checkbox" wire:model="serviceCategoryIsActive" class="toggle toggle-primary" />
                                    <span class="label-text font-medium">Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if($serviceCategoryId)
                                <button type="button" wire:click="resetServiceCategoryForm" class="btn btn-ghost">Cancel</button>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-lg px-5">{{ $serviceCategoryId ? 'Update' : 'Create' }} Category</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border border-base-300">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($serviceCategories as $category)
                                    <tr>
                                        <td class="font-semibold">{{ $category->name }}</td>
                                        <td>{{ $category->description ?: '-' }}</td>
                                        <td>
                                            <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-warning' }} badge-sm">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-end">
                                                <button tabindex="0" type="button" class="btn btn-ghost btn-sm btn-circle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" />
                                                    </svg>
                                                </button>
                                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                    <li>
                                                        <button type="button" wire:click="editServiceCategory({{ $category->id }})">Edit</button>
                                                    </li>
                                                    <li>
                                                        <button type="button" onclick="if (confirm('Delete this service category?')) { $wire.deleteServiceCategory({{ $category->id }}) }" class="text-error">Delete</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-10 text-base-content/60">No service categories found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
