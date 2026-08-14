<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Service Types</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Set the core price and duration used by job cards and other service flows.</p>
    </div>

    @if (session()->has('message'))
        <div class="gh-badge gh-badge--success" style="font-size:12px; padding:8px 12px; width:fit-content;">{{ session('message') }}</div>
    @endif

    <div class="gh-card gh-card--pad">
        <form wire:submit.prevent="{{ $editMode ? 'update' : 'create' }}" class="gh-stack">
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">Name *</span>
                    <input type="text" wire:model.defer="name" class="gh-input" style="width:100%;">
                    @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Price *</span>
                    <input type="number" step="0.01" wire:model.defer="price" class="gh-input" style="width:100%;">
                    @error('price') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Estimated duration (min) *</span>
                    <input type="number" wire:model.defer="estimated_duration" class="gh-input" style="width:100%;">
                    @error('estimated_duration') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div style="display:flex; align-items:center;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:16px;">
                        <input type="checkbox" wire:model.defer="is_active" id="is_active">
                        <span style="font-weight:600; font-size:12.5px;">Active</span>
                    </label>
                </div>
            </div>
            <div class="gh-field">
                <span class="gh-label">Description</span>
                <textarea wire:model.defer="description" rows="3" class="gh-input" style="width:100%;"></textarea>
                @error('description') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="gh-btn gh-btn--primary">{{ $editMode ? 'Update' : 'Add' }} Service Type</button>
                @if ($editMode)
                    <button type="button" wire:click="resetForm" class="gh-btn">Cancel</button>
                @endif
            </div>
        </form>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th class="is-index">#</th>
                        <th>Service</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceTypes as $type)
                        <tr>
                            <td class="is-index">{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight:700;">{{ $type->name }}</div>
                                <div class="gh-muted" style="font-size:10.5px;">ID: #S{{ str_pad($type->id, 3, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="gh-muted" style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $type->description }}</td>
                            <td style="font-family:monospace;">UGX {{ number_format($type->price, 2) }}</td>
                            <td class="gh-muted">{{ $type->estimated_duration }} min</td>
                            <td>
                                <span class="gh-badge {{ $type->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td style="text-align:right;">
                                <button wire:click="edit({{ $type->id }})" class="gh-btn gh-btn--sm" title="Edit">Edit</button>
                                <button wire:click="confirmDelete({{ $type->id }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);" title="Delete">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No service types found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($serviceTypeId && !$editMode)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Delete Service Type</div>
                <p class="gh-muted" style="font-size:12.5px;">Are you sure you want to delete this service type? This action cannot be undone.</p>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="resetForm" class="gh-btn">Cancel</button>
                    <button wire:click="delete" class="gh-btn gh-btn--primary" style="background:var(--gh-error); border-color:var(--gh-error);">Delete</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="resetForm"></div>
        </div>
    @endif
</div>
