<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Suppliers</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage parts and supplies vendors</p>
        </div>
        @can('create_suppliers')
            <button wire:click="$set('showCreateModal', true)" class="gh-btn gh-btn--primary">+ Add supplier</button>
        @endcan
    </div>

    <div class="gh-table-toolbar">
        <label class="gh-search" style="width:220px;">⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search…"></label>
        @if($search)
            <button wire:click="$set('search', '')" class="gh-btn gh-btn--sm">Clear</button>
        @endif
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Items</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr data-href="{{ route('suppliers.show', $supplier) }}">
                            <td>
                                <div class="gh-cell-stack">
                                    <b>{{ $supplier->name }}</b>
                                    @if($supplier->address)<span>{{ Str::limit($supplier->address, 40) }}</span>@endif
                                </div>
                            </td>
                            <td>{{ $supplier->contact_person ?? '—' }}</td>
                            <td>
                                <div class="gh-cell-stack">
                                    <b>{{ $supplier->phone }}</b>
                                    @if($supplier->email)<span>{{ $supplier->email }}</span>@endif
                                </div>
                            </td>
                            <td><span class="gh-badge">{{ $supplier->inventory_movements_count }}</span></td>
                            <td><span class="gh-badge {{ $supplier->is_active ? 'gh-badge--success' : 'gh-badge--error' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td onclick="event.stopPropagation()">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-40 gh-card p-2 shadow-xl">
                                        <li><a href="{{ route('suppliers.show', $supplier) }}">View</a></li>
                                        <li><button wire:click="toggleStatus({{ $supplier->id }})" style="color:{{ $supplier->is_active ? 'var(--gh-error)' : 'var(--gh-success)' }};">{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}</button></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No suppliers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="gh-pagination">{{ $suppliers->links() }}</div>
        @endif
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:16px;">Add Supplier</div>
                <form wire:submit="createSupplier">
                    <div class="gh-field" style="margin-bottom:12px;">
                        <span class="gh-label">Supplier name *</span>
                        <input type="text" wire:model="name" class="gh-input" style="width:100%;">
                        @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field" style="margin-bottom:12px;">
                        <span class="gh-label">Contact person</span>
                        <input type="text" wire:model="contact_person" class="gh-input" style="width:100%;">
                    </div>
                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Phone *</span>
                            <input type="text" wire:model="phone" class="gh-input" style="width:100%;">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Email</span>
                            <input type="email" wire:model="email" class="gh-input" style="width:100%;">
                        </div>
                    </div>
                    <div class="gh-field" style="margin-top:12px;">
                        <span class="gh-label">Address</span>
                        <textarea wire:model="address" rows="2" class="gh-input" style="width:100%;"></textarea>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="gh-btn">Cancel</button>
                        <button type="submit" class="gh-btn gh-btn--primary">Create supplier</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showCreateModal', false)"></div>
        </div>
    @endif
</div>
