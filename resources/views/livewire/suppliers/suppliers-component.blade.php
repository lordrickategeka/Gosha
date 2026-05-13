<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Suppliers</h1>
            <p class="text-base-content/60">Manage parts and supplies vendors</p>
        </div>
        @can('create_suppliers')
        <button wire:click="$set('showCreateModal', true)" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Supplier
        </button>
        @endcan
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search suppliers..." class="input input-bordered w-full max-w-md" />
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr class="hover">
                            <td>
                                <div class="font-bold">{{ $supplier->name }}</div>
                                @if($supplier->address)
                                    <p class="text-xs text-base-content/60">{{ Str::limit($supplier->address, 40) }}</p>
                                @endif
                            </td>
                            <td>{{ $supplier->contact_person ?? '-' }}</td>
                            <td>
                                <p>{{ $supplier->phone }}</p>
                                @if($supplier->email)
                                    <p class="text-xs text-base-content/60">{{ $supplier->email }}</p>
                                @endif
                            </td>
                            <td><span class="badge badge-ghost">{{ $supplier->inventory_movements_count }}</span></td>
                            <td>
                                <span class="badge badge-{{ $supplier->is_active ? 'success' : 'error' }} badge-sm">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40">
                                        <li><a href="{{ route('suppliers.show', $supplier) }}">View</a></li>
                                        <li>
                                            <button wire:click="toggleStatus({{ $supplier->id }})" class="{{ $supplier->is_active ? 'text-error' : 'text-success' }}">
                                                {{ $supplier->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-base-content/50">No suppliers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="p-4 border-t border-base-200">{{ $suppliers->links() }}</div>
        @endif
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box app-modal-shell">
                <h3 class="font-bold text-lg mb-4">Add Supplier</h3>
                <form wire:submit="createSupplier">
                    <div class="form-control mb-3">
                        <label class="label"><span class="label-text">Supplier Name *</span></label>
                        <input type="text" wire:model="name" class="input input-bordered" />
                        @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control mb-3">
                        <label class="label"><span class="label-text">Contact Person</span></label>
                        <input type="text" wire:model="contact_person" class="input input-bordered" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Phone *</span></label>
                            <input type="text" wire:model="phone" class="input input-bordered" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Email</span></label>
                            <input type="email" wire:model="email" class="input input-bordered" />
                        </div>
                    </div>
                    <div class="form-control mt-3">
                        <label class="label"><span class="label-text">Address</span></label>
                        <textarea wire:model="address" rows="2" class="textarea textarea-bordered"></textarea>
                    </div>
                    <div class="modal-action app-modal-actions">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Supplier</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="$set('showCreateModal', false)"></div>
        </div>
    @endif
</div>
