<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Wash Packages</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Define customer-ready wash offers, their included work, price and estimated time.</p>
        </div>
        <button wire:click="create" class="gh-btn gh-btn--primary">+ New package</button>
    </div>

    <div class="gh-card gh-card--pad">
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search packages..." class="gh-input" style="flex:1; min-width:200px;">
            <select wire:model.live="filterType" class="gh-select">
                <option value="">All Types</option>
                <option value="basic">Basic</option>
                <option value="standard">Standard</option><option value="custom">Custom</option>
                <option value="premium">Premium</option>
                <option value="interior">Interior</option>
                <option value="exterior">Exterior</option>
                <option value="engine">Engine</option>
                <option value="full_detail">Full detail</option>
            </select>
            <select wire:model.live="filterStatus" class="gh-select">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <x-service-configuration-help topic="wash" />
    <div class="gh-card gh-card--flush"><div class="gh-table-scroll"><table class="gh-table"><caption class="sr-only">Wash package price list</caption><thead><tr><th>Package</th><th>Type</th><th>Included tasks</th><th>Time</th><th>Price (UGX)</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @forelse($packages as $package)<tr wire:key="package-{{ $package->id }}"><td style="max-width:270px;white-space:normal;"><strong>{{ $package->name }}</strong><p class="gh-muted">{{ $package->description }}</p></td><td>{{ ucfirst(str_replace('_', ' ', $package->wash_type)) }}</td><td style="max-width:300px;white-space:normal;">{{ implode(', ', $package->includes_list) ?: 'No inclusions recorded' }}</td><td>{{ $package->duration_display }}</td><td>{{ number_format($package->price, 2) }}</td><td><span class="gh-badge {{ $package->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span></td><td style="white-space:nowrap;"><button type="button" wire:click="toggleStatus({{ $package->id }})" class="gh-btn gh-btn--sm">{{ $package->is_active ? 'Deactivate' : 'Activate' }}</button> <button type="button" wire:click="edit({{ $package->id }})" class="gh-btn gh-btn--sm">Edit</button> <button type="button" wire:click="delete({{ $package->id }})" wire:confirm="Delete this package? This cannot be undone." class="gh-btn gh-btn--sm">Delete</button></td></tr>
    @empty<tr><td colspan="7" style="padding:32px;">No packages match this list. Clear the filters or add your first wash package, such as a standard wash with body cleaning and vacuum.</td></tr>@endforelse
    </tbody></table></div></div>
    @if($packages->hasPages())<div>{{ $packages->links() }}</div>@endif

    <!-- Create / Edit Modal -->
    @if($showModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:42rem;">
                <div class="gh-card__title" style="margin-bottom:16px;">{{ $editingId ? 'Edit Package' : 'New Wash Package' }}</div>

                <div class="gh-grid-2">
                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Package name *</span>
                        <input type="text" wire:model="name" placeholder="e.g. Premium Full Wash" class="gh-input" style="width:100%;">
                        @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Wash type *</span>
                        <select wire:model="wash_type" class="gh-select" style="width:100%;">
                            <option value="basic">Basic</option>
                            <option value="standard">Standard</option><option value="custom">Custom</option>
                            <option value="premium">Premium</option>
                            <option value="interior">Interior</option>
                            <option value="exterior">Exterior</option>
                            <option value="engine">Engine</option>
                            <option value="full_detail">Full detail</option>
                        </select>
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Price *</span>
                        <input type="number" wire:model="price" placeholder="0.00" step="0.01" min="0" class="gh-input" style="width:100%;">
                        @error('price') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Duration (minutes) *</span>
                        <input type="number" wire:model="estimated_duration_minutes" placeholder="30" min="5" class="gh-input" style="width:100%;">
                        @error('estimated_duration_minutes') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Sort order</span>
                        <input type="number" wire:model="sort_order" placeholder="0" min="0" class="gh-input" style="width:100%;">
                    </div>

                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Description</span>
                        <textarea wire:model="description" rows="2" placeholder="Brief description of the package..." class="gh-input" style="width:100%;"></textarea>
                    </div>

                    <div style="grid-column:1/-1;">
                        <span class="gh-label" style="display:block; margin-bottom:6px;">What's included</span>
                        <div style="display:flex; gap:8px; margin-bottom:8px;">
                            <input type="text" wire:model="newIncludeItem" wire:keydown.enter.prevent="addIncludeItem" placeholder="e.g. Exterior wash, Interior vacuum..." class="gh-input" style="flex:1;">
                            <button type="button" wire:click="addIncludeItem" class="gh-btn gh-btn--sm">Add</button>
                        </div>
                        @if($includes)
                            <ul class="gh-stack" style="gap:6px;">
                                @foreach($includes as $i => $item)
                                    <li style="display:flex; align-items:center; justify-content:space-between; background:var(--gh-base-200); border-radius:var(--gh-radius); padding:6px 10px;">
                                        <span style="font-size:12.5px;">{{ $item }}</span>
                                        <button type="button" wire:click="removeIncludeItem({{ $i }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">✕</button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div style="grid-column:1/-1;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" wire:model="is_active">
                            <span style="font-weight:600; font-size:12.5px;">Package is active (visible when creating wash orders)</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button type="button" wire:click="closeModal" class="gh-btn">Cancel</button>
                    <button type="button" wire:click="save" class="gh-btn gh-btn--primary">{{ $editingId ? 'Update package' : 'Create package' }}</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeModal"></div>
        </div>
    @endif
</div>
