<div class="gh-stack">
    <div class="gh-card gh-card--pad">
        <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:14px;">
            <div>
                <div class="gh-eyebrow">System taxonomy</div>
                <div class="gh-card__title" style="margin-top:4px;">Taxonomy Settings</div>
                <p class="gh-muted" style="font-size:12px; margin-top:4px;">Manage inventory and service classification from one place.</p>
            </div>
            @if (session()->has('message'))
                <div class="gh-badge gh-badge--success" style="font-size:12px; padding:8px 12px;">{{ session('message') }}</div>
            @endif
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:16px;">
            <button type="button" wire:click="setSection('inventory_categories')" class="gh-chip gh-chip--round {{ $section === 'inventory_categories' ? 'is-active' : '' }}">
                Inventory Categories <span class="gh-badge {{ $section === 'inventory_categories' ? 'gh-badge--primary' : '' }}" style="margin-left:4px;">{{ $inventoryCategories->total() }}</span>
            </button>
            <button type="button" wire:click="setSection('inventory_types')" class="gh-chip gh-chip--round {{ $section === 'inventory_types' ? 'is-active' : '' }}">
                Inventory Types <span class="gh-badge {{ $section === 'inventory_types' ? 'gh-badge--primary' : '' }}" style="margin-left:4px;">{{ $inventoryTypes->count() }}</span>
            </button>
            <button type="button" wire:click="setSection('service_types')" class="gh-chip gh-chip--round {{ $section === 'service_types' ? 'is-active' : '' }}">
                Service Types <span class="gh-badge {{ $section === 'service_types' ? 'gh-badge--primary' : '' }}" style="margin-left:4px;">{{ $serviceTypes->count() }}</span>
            </button>
            <button type="button" wire:click="setSection('service_categories')" class="gh-chip gh-chip--round {{ $section === 'service_categories' ? 'is-active' : '' }}">
                Service Categories <span class="gh-badge {{ $section === 'service_categories' ? 'gh-badge--primary' : '' }}" style="margin-left:4px;">{{ $serviceCategories->count() }}</span>
            </button>
        </div>
    </div>

    @if($section === 'inventory_categories')
        <div class="gh-grid-2">
            <div class="gh-card gh-card--pad">
                <div style="margin-bottom:14px;">
                    <div class="gh-card__title">{{ $inventoryCategoryId ? 'Edit Inventory Category' : 'New Inventory Category' }}</div>
                    <p class="gh-muted" style="font-size:12px; margin-top:2px;">Categories are scoped to your vendor and can auto-generate a code if left blank.</p>
                </div>

                <form wire:submit.prevent="saveInventoryCategory" class="gh-stack">
                    <div class="gh-grid-2">
                        <div class="gh-field" style="grid-column:1/-1;">
                            <span class="gh-label">Name *</span>
                            <input type="text" wire:model.live="inventoryCategoryName" class="gh-input" style="width:100%;">
                            @error('inventoryCategoryName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Type *</span>
                            <select wire:model.live="inventoryCategoryType" class="gh-select" style="width:100%;">
                                <option value="service_parts">Service Parts</option>
                                <option value="wash_supplies">Wash Supplies</option>
                                <option value="consumables">Consumables</option>
                                <option value="tools">Tools</option>
                            </select>
                            @error('inventoryCategoryType') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Parent Category</span>
                            <select wire:model="inventoryCategoryParentId" class="gh-select" style="width:100%;">
                                <option value="">None</option>
                                @foreach($inventoryCategoryParents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            @error('inventoryCategoryParentId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Code</span>
                            <input type="text" wire:model.defer="inventoryCategoryCode" class="gh-input" style="width:100%;" placeholder="Auto-generated if left blank">
                            <span class="gh-hint">If blank, the system will generate a code on save.</span>
                            @error('inventoryCategoryCode') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field" style="grid-column:1/-1;">
                            <span class="gh-label">Description</span>
                            <textarea wire:model.defer="inventoryCategoryDescription" rows="3" class="gh-input" style="width:100%;"></textarea>
                            @error('inventoryCategoryDescription') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div style="grid-column:1/-1;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="checkbox" wire:model="inventoryCategoryIsActive">
                                <span style="font-weight:600; font-size:12.5px;">Active</span>
                            </label>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        @if($inventoryCategoryId)
                            <button type="button" wire:click="resetInventoryCategoryForm" class="gh-btn">Cancel</button>
                        @endif
                        <button type="submit" class="gh-btn gh-btn--primary">{{ $inventoryCategoryId ? 'Update' : 'Create' }} Category</button>
                    </div>
                </form>
            </div>

            <div class="gh-card gh-card--flush">
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Code</th>
                                <th>Parent</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryCategories as $category)
                                <tr>
                                    <td style="font-weight:700;">{{ $category->name }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $category->type)) }}</td>
                                    <td class="gh-muted" style="font-family:monospace; font-size:11px;">{{ $category->code ?? 'Auto' }}</td>
                                    <td class="gh-muted">{{ $category->parent?->name ?? '-' }}</td>
                                    <td>
                                        <span class="gh-badge {{ $category->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                <li><button type="button" wire:click="editInventoryCategory({{ $category->id }})">Edit</button></li>
                                                <li><button type="button" onclick="if (confirm('Delete this inventory category?')) { $wire.deleteInventoryCategory({{ $category->id }}) }" style="color:var(--gh-error);">Delete</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No inventory categories found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">
                    {{ $inventoryCategories->links() }}
                </div>
            </div>
        </div>
    @endif

    @if($section === 'inventory_types')
        <div class="gh-grid-2">
            <div class="gh-card gh-card--pad">
                <div style="margin-bottom:14px;">
                    <div class="gh-card__title">{{ $inventoryTypeId ? 'Edit Inventory Type' : 'New Inventory Type' }}</div>
                    <p class="gh-muted" style="font-size:12px; margin-top:2px;">Inventory types live under a chosen inventory category.</p>
                </div>

                <form wire:submit.prevent="saveInventoryType" class="gh-stack">
                    <div class="gh-field">
                        <span class="gh-label">Name *</span>
                        <input type="text" wire:model.defer="inventoryTypeName" class="gh-input" style="width:100%;">
                        @error('inventoryTypeName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Inventory Category *</span>
                        <select wire:model="inventoryTypeCategoryId" class="gh-select" style="width:100%;">
                            <option value="">Select category...</option>
                            @foreach($inventoryTypeCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }} ({{ str_replace('_', ' ', $category->type) }})</option>
                            @endforeach
                        </select>
                        @error('inventoryTypeCategoryId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        @if($inventoryTypeId)
                            <button type="button" wire:click="resetInventoryTypeForm" class="gh-btn">Cancel</button>
                        @endif
                        <button type="submit" class="gh-btn gh-btn--primary">{{ $inventoryTypeId ? 'Update' : 'Create' }} Type</button>
                    </div>
                </form>
            </div>

            <div class="gh-card gh-card--flush">
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Name</th><th>Category</th><th></th></tr></thead>
                        <tbody>
                            @forelse($inventoryTypes as $type)
                                <tr>
                                    <td style="font-weight:700;">{{ $type->name }}</td>
                                    <td class="gh-muted">{{ $type->category?->name ?? '-' }}</td>
                                    <td style="text-align:right;">
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                <li><button type="button" wire:click="editInventoryType({{ $type->id }})">Edit</button></li>
                                                <li><button type="button" onclick="if (confirm('Delete this inventory type?')) { $wire.deleteInventoryType({{ $type->id }}) }" style="color:var(--gh-error);">Delete</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No inventory types found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($section === 'service_types')
        <div class="gh-grid-2">
            <div class="gh-card gh-card--pad">
                <div style="margin-bottom:14px;">
                    <div class="gh-card__title">{{ $serviceTypeId ? 'Edit Service Type' : 'New Service Type' }}</div>
                    <p class="gh-muted" style="font-size:12px; margin-top:2px;">Set the core price and duration used by job cards and other service flows.</p>
                </div>

                <form wire:submit.prevent="saveServiceType" class="gh-stack">
                    <div class="gh-grid-2">
                        <div class="gh-field" style="grid-column:1/-1;">
                            <span class="gh-label">Name *</span>
                            <input type="text" wire:model.defer="serviceTypeName" class="gh-input" style="width:100%;">
                            @error('serviceTypeName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Price (UGX) *</span>
                            <input type="number" wire:model.defer="serviceTypePrice" min="0" step="0.01" class="gh-input" style="width:100%;">
                            @error('serviceTypePrice') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>

                        <div class="gh-field">
                            <span class="gh-label">Duration (minutes) *</span>
                            <input type="number" wire:model.defer="serviceTypeEstimatedDuration" min="1" class="gh-input" style="width:100%;">
                            @error('serviceTypeEstimatedDuration') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        @if($serviceTypeId)
                            <button type="button" wire:click="resetServiceTypeForm" class="gh-btn">Cancel</button>
                        @endif
                        <button type="submit" class="gh-btn gh-btn--primary">{{ $serviceTypeId ? 'Update' : 'Create' }} Service Type</button>
                    </div>
                </form>
            </div>

            <div class="gh-card gh-card--flush">
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Name</th><th>Price</th><th>Duration</th><th></th></tr></thead>
                        <tbody>
                            @forelse($serviceTypes as $type)
                                <tr>
                                    <td style="font-weight:700;">{{ $type->name }}</td>
                                    <td class="is-num">UGX {{ number_format($type->price, 2) }}</td>
                                    <td class="gh-muted">{{ $type->estimated_duration }} min</td>
                                    <td style="text-align:right;">
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                <li><button type="button" wire:click="editServiceType({{ $type->id }})">Edit</button></li>
                                                <li><button type="button" onclick="if (confirm('Delete this service type?')) { $wire.deleteServiceType({{ $type->id }}) }" style="color:var(--gh-error);">Delete</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No service types found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($section === 'service_categories')
        <div class="gh-grid-2">
            <div class="gh-card gh-card--pad">
                <div style="margin-bottom:14px;">
                    <div class="gh-card__title">{{ $serviceCategoryId ? 'Edit Service Category' : 'New Service Category' }}</div>
                    <p class="gh-muted" style="font-size:12px; margin-top:2px;">Group services into a clean naming structure for scheduling and reporting.</p>
                </div>

                <form wire:submit.prevent="saveServiceCategory" class="gh-stack">
                    <div class="gh-field">
                        <span class="gh-label">Name *</span>
                        <input type="text" wire:model.defer="serviceCategoryName" class="gh-input" style="width:100%;">
                        @error('serviceCategoryName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Description</span>
                        <textarea wire:model.defer="serviceCategoryDescription" rows="3" class="gh-input" style="width:100%;"></textarea>
                        @error('serviceCategoryDescription') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" wire:model="serviceCategoryIsActive">
                            <span style="font-weight:600; font-size:12.5px;">Active</span>
                        </label>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        @if($serviceCategoryId)
                            <button type="button" wire:click="resetServiceCategoryForm" class="gh-btn">Cancel</button>
                        @endif
                        <button type="submit" class="gh-btn gh-btn--primary">{{ $serviceCategoryId ? 'Update' : 'Create' }} Category</button>
                    </div>
                </form>
            </div>

            <div class="gh-card gh-card--flush">
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Name</th><th>Description</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($serviceCategories as $category)
                                <tr>
                                    <td style="font-weight:700;">{{ $category->name }}</td>
                                    <td class="gh-muted">{{ $category->description ?: '-' }}</td>
                                    <td>
                                        <span class="gh-badge {{ $category->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40 border border-base-300">
                                                <li><button type="button" wire:click="editServiceCategory({{ $category->id }})">Edit</button></li>
                                                <li><button type="button" onclick="if (confirm('Delete this service category?')) { $wire.deleteServiceCategory({{ $category->id }}) }" style="color:var(--gh-error);">Delete</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No service categories found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
