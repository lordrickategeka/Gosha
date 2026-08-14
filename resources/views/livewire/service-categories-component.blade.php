<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Service Categories</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Group services into a clean naming structure for scheduling and reporting.</p>
        </div>
        <button wire:click="openCreateForm" class="gh-btn gh-btn--primary">+ New category</button>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th class="is-index">#</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceCategories as $category)
                        <tr>
                            <td class="is-index">{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight:700;">{{ $category->name }}</div>
                                <div class="gh-muted" style="font-size:10.5px;">ID: #SC{{ str_pad($category->id, 3, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="gh-muted" style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $category->description ?: '-' }}</td>
                            <td>
                                <span class="gh-badge {{ $category->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td style="text-align:right;">
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44 border border-base-300">
                                        <li><a wire:click="edit({{ $category->id }})">Edit</a></li>
                                        <li><a wire:click="confirmDelete({{ $category->id }})" style="color:var(--gh-error);">Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No service categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showFormModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:16px;">{{ $editMode ? 'Edit Service Category' : 'Create Service Category' }}</div>
                <form wire:submit.prevent="{{ $editMode ? 'update' : 'create' }}">
                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Category name *</span>
                            <input type="text" wire:model.defer="name" class="gh-input" style="width:100%;">
                            @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                        </div>
                        <div style="display:flex; align-items:center;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:16px;">
                                <input type="checkbox" wire:model.defer="is_active" id="category_is_active">
                                <span style="font-weight:600; font-size:12.5px;">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="gh-field" style="margin-top:12px;">
                        <span class="gh-label">Description</span>
                        <textarea wire:model.defer="description" rows="3" class="gh-input" style="width:100%;"></textarea>
                        @error('description') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                        <button type="button" wire:click="resetForm" class="gh-btn">Cancel</button>
                        <button type="submit" class="gh-btn gh-btn--primary">{{ $editMode ? 'Update' : 'Create' }} category</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="resetForm"></div>
        </div>
    @endif

    @if($serviceCategoryId && !$editMode)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Delete Service Category</div>
                <p class="gh-muted" style="font-size:12.5px;">Are you sure you want to delete this service category? This action cannot be undone.</p>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="resetForm" class="gh-btn">Cancel</button>
                    <button wire:click="delete" class="gh-btn gh-btn--primary" style="background:var(--gh-error); border-color:var(--gh-error);">Delete</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="resetForm"></div>
        </div>
    @endif
</div>
