<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Bay Status</h1>
        <p class="text-base-content/60">Real-time view of all service and wash bays</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Service Bays -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Service Bays ({{ $this->serviceBays->count() }})
                </h2>
                @can('manage bays')
                <button wire:click="createServiceBay" class="btn btn-primary btn-sm gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Bay
                </button>
                @endcan
            </div>

            @if($this->serviceBays->count() === 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-base-content/30 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="text-base-content/50">No service bays yet</p>
                        @can('manage bays')
                        <button wire:click="createServiceBay" class="btn btn-primary btn-sm mt-3">Add Your First Service Bay</button>
                        @endcan
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                @foreach($this->serviceBays as $bay)
                    <div class="card bg-base-100 shadow-sm border-l-4 {{ $bay->status === 'available' ? 'border-success' : ($bay->status === 'occupied' ? 'border-warning' : 'border-error') }}">
                        <div class="card-body p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-bold">{{ $bay->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="badge badge-{{ $bay->status === 'available' ? 'success' : ($bay->status === 'occupied' ? 'warning' : 'error') }} badge-sm">
                                            {{ ucfirst($bay->status) }}
                                        </span>
                                        <span class="text-xs text-base-content/50">{{ ucfirst($bay->bay_type) }}</span>
                                    </div>
                                </div>

                                @can('manage bays')
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44">
                                        <li><button wire:click="editServiceBay({{ $bay->id }})">Edit</button></li>
                                        @if($bay->status !== 'available')
                                            <li><button wire:click="markServiceBayAvailable({{ $bay->id }})">Mark Available</button></li>
                                        @endif
                                        @if($bay->status !== 'maintenance')
                                            <li><button wire:click="markServiceBayMaintenance({{ $bay->id }})">Set Maintenance</button></li>
                                        @endif
                                        @if(!$bay->isOccupied())
                                            <li><button wire:click="confirmDelete({{ $bay->id }}, 'service')" class="text-error">Delete</button></li>
                                        @endif
                                    </ul>
                                </div>
                                @endcan
                            </div>

                            @if($bay->currentWorkOrder)
                                <div class="mt-3 p-3 bg-base-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-mono text-sm">{{ $bay->currentWorkOrder->order_number }}</p>
                                            <p class="font-bold">{{ $bay->currentWorkOrder->vehicle?->registration_number }}</p>
                                            <p class="text-sm text-base-content/60">{{ $bay->currentWorkOrder->vehicle?->make }} {{ $bay->currentWorkOrder->vehicle?->model }}</p>
                                        </div>
                                        <a href="{{ route('work-orders.show', $bay->currentWorkOrder) }}" class="btn btn-primary btn-sm">View</a>
                                    </div>
                                    @if($bay->currentWorkOrder->assignedTechnician)
                                        <div class="flex items-center gap-2 mt-2 text-sm">
                                            <div class="avatar placeholder">
                                                <div class="bg-neutral text-neutral-content rounded-full w-6">
                                                    <span class="text-xs">{{ substr($bay->currentWorkOrder->assignedTechnician->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <span>{{ $bay->currentWorkOrder->assignedTechnician->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="mt-3 text-base-content/50 text-sm">Ready for next vehicle</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Wash Bays -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Wash Bays ({{ $this->washBays->count() }})
                </h2>
                @can('manage bays')
                <button wire:click="createWashBay" class="btn btn-info btn-sm gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Bay
                </button>
                @endcan
            </div>

            @if($this->washBays->count() === 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-base-content/30 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <p class="text-base-content/50">No wash bays yet</p>
                        @can('manage bays')
                        <button wire:click="createWashBay" class="btn btn-info btn-sm mt-3">Add Your First Wash Bay</button>
                        @endcan
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                @foreach($this->washBays as $bay)
                    <div class="card bg-base-100 shadow-sm border-l-4 {{ $bay->status === 'available' ? 'border-success' : ($bay->status === 'occupied' ? 'border-info' : 'border-error') }}">
                        <div class="card-body p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-bold">{{ $bay->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="badge badge-{{ $bay->status === 'available' ? 'success' : ($bay->status === 'occupied' ? 'info' : 'error') }} badge-sm">
                                            {{ ucfirst($bay->status) }}
                                        </span>
                                        <span class="text-xs text-base-content/50">{{ ucfirst($bay->bay_type) }}</span>
                                    </div>
                                </div>

                                @can('manage bays')
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44">
                                        <li><button wire:click="editWashBay({{ $bay->id }})">Edit</button></li>
                                        @if($bay->status !== 'available')
                                            <li><button wire:click="markWashBayAvailable({{ $bay->id }})">Mark Available</button></li>
                                        @endif
                                        @if($bay->status !== 'maintenance')
                                            <li><button wire:click="markWashBayMaintenance({{ $bay->id }})">Set Maintenance</button></li>
                                        @endif
                                        @if(!$bay->isOccupied())
                                            <li><button wire:click="confirmDelete({{ $bay->id }}, 'wash')" class="text-error">Delete</button></li>
                                        @endif
                                    </ul>
                                </div>
                                @endcan
                            </div>

                            @if($bay->currentWashOrder)
                                <div class="mt-3 p-3 bg-base-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-mono text-sm">{{ $bay->currentWashOrder->order_number }}</p>
                                            <p class="font-bold">{{ $bay->currentWashOrder->vehicle?->registration_number }}</p>
                                            <p class="text-sm text-base-content/60">{{ ucfirst($bay->currentWashOrder->wash_type) }}</p>
                                        </div>
                                        <a href="{{ route('wash-orders.show', $bay->currentWashOrder) }}" class="btn btn-info btn-sm">View</a>
                                    </div>
                                    @if($bay->currentWashOrder->started_at)
                                        <p class="text-xs text-base-content/50 mt-2">Started {{ $bay->currentWashOrder->started_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="mt-3 text-base-content/50 text-sm">Ready for next vehicle</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Service Bay Modal -->
    @if($showServiceBayModal)
    <div class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{ $editingServiceBayId ? 'Edit Service Bay' : 'Add Service Bay' }}</h3>
            <div class="py-4 space-y-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Bay Name *</span></label>
                    <input type="text" wire:model="serviceBayName" class="input input-bordered" placeholder="e.g. Service Bay 1" />
                    @error('serviceBayName') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Bay Type *</span></label>
                    <select wire:model="serviceBayType" class="select select-bordered">
                        <option value="general">General</option>
                        <option value="electrical">Electrical</option>
                        <option value="bodywork">Bodywork</option>
                        <option value="diagnostics">Diagnostics</option>
                        <option value="ac">AC</option>
                        <option value="tyres">Tyres</option>
                    </select>
                    @error('serviceBayType') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Notes</span></label>
                    <textarea wire:model="serviceBayNotes" class="textarea textarea-bordered" rows="2" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-action">
                <button wire:click="$set('showServiceBayModal', false)" class="btn btn-ghost">Cancel</button>
                <button wire:click="saveServiceBay" class="btn btn-primary">
                    {{ $editingServiceBayId ? 'Update' : 'Create' }}
                </button>
            </div>
        </div>
        <div class="modal-backdrop" wire:click="$set('showServiceBayModal', false)"></div>
    </div>
    @endif

    <!-- Wash Bay Modal -->
    @if($showWashBayModal)
    <div class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{ $editingWashBayId ? 'Edit Wash Bay' : 'Add Wash Bay' }}</h3>
            <div class="py-4 space-y-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Bay Name *</span></label>
                    <input type="text" wire:model="washBayName" class="input input-bordered" placeholder="e.g. Wash Bay 1" />
                    @error('washBayName') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Bay Type *</span></label>
                    <select wire:model="washBayType" class="select select-bordered">
                        <option value="standard">Standard</option>
                        <option value="premium">Premium</option>
                        <option value="detailing">Detailing</option>
                    </select>
                    @error('washBayType') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Notes</span></label>
                    <textarea wire:model="washBayNotes" class="textarea textarea-bordered" rows="2" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="modal-action">
                <button wire:click="$set('showWashBayModal', false)" class="btn btn-ghost">Cancel</button>
                <button wire:click="saveWashBay" class="btn btn-info">
                    {{ $editingWashBayId ? 'Update' : 'Create' }}
                </button>
            </div>
        </div>
        <div class="modal-backdrop" wire:click="$set('showWashBayModal', false)"></div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeleteId)
    <div class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-error">Delete Bay</h3>
            <p class="py-4">Are you sure you want to delete this bay? This action cannot be undone.</p>
            <div class="modal-action">
                <button wire:click="cancelDelete" class="btn btn-ghost">Cancel</button>
                <button wire:click="deleteBay" class="btn btn-error">Delete</button>
            </div>
        </div>
        <div class="modal-backdrop" wire:click="cancelDelete"></div>
    </div>
    @endif
</div>
