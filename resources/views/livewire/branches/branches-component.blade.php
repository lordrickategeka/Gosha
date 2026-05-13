<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Branches</h1>
            <p class="text-base-content/60">Manage your garage locations</p>
        </div>
        @can('create_branches')
        <button wire:click="$set('showCreateModal', true)" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Branch
        </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($this->branches as $branch)
            <div class="card bg-base-100 shadow-sm {{ session('current_branch_id') == $branch->id ? 'ring-2 ring-primary' : '' }}">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="card-title">{{ $branch->name }}</h2>
                            @if($branch->address)
                                <p class="text-sm text-base-content/60">{{ $branch->address }}</p>
                            @endif
                        </div>
                        @if(session('current_branch_id') == $branch->id)
                            <span class="badge badge-primary">Current</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-2 mt-4">
                        <div class="text-center p-2 bg-base-200 rounded">
                            <p class="text-lg font-bold">{{ $branch->work_orders_count }}</p>
                            <p class="text-xs text-base-content/60">Work Orders</p>
                        </div>
                        <div class="text-center p-2 bg-base-200 rounded">
                            <p class="text-lg font-bold">{{ $branch->wash_orders_count }}</p>
                            <p class="text-xs text-base-content/60">Washes</p>
                        </div>
                        <div class="text-center p-2 bg-base-200 rounded">
                            <p class="text-lg font-bold">{{ $branch->users_count }}</p>
                            <p class="text-xs text-base-content/60">Staff</p>
                        </div>
                    </div>

                    <div class="card-actions justify-end mt-4">
                        @if(session('current_branch_id') != $branch->id)
                            <button wire:click="switchBranch({{ $branch->id }})" class="btn btn-primary btn-sm">Switch</button>
                        @endif
                        @can('edit_branches')
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-ghost btn-sm">Edit</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box app-modal-shell">
                <h3 class="font-bold text-lg mb-4">Add Branch</h3>
                <form wire:submit="createBranch">
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Branch Name *</span></label>
                        <input type="text" wire:model="name" class="input input-bordered" placeholder="Main Branch" />
                        @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Address</span></label>
                        <input type="text" wire:model="address" class="input input-bordered" placeholder="123 Main Street" />
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Phone</span></label>
                        <input type="text" wire:model="phone" class="input input-bordered" placeholder="+256 700 000000" />
                    </div>
                    <div class="modal-action app-modal-actions">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Branch</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="$set('showCreateModal', false)"></div>
        </div>
    @endif
</div>
