<div class="space-y-6">
    <div class="app-page-header">
        <div>
            <p class="app-kicker">Wash operations</p>
            <h1 class="app-title">Wash Bay</h1>
            <p class="app-subtitle">Manage live wash queue activity, assign bays, and complete orders quickly.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="app-segmented">
                <button wire:click="$set('view', 'queue')" class="app-segment-button {{ $view === 'queue' ? 'is-active' : '' }}">
                    Queue View
                </button>
                <button wire:click="$set('view', 'list')" class="app-segment-button {{ $view === 'list' ? 'is-active' : '' }}">
                    List View
                </button>
            </div>

            @can('create_wash_orders')
            <a href="{{ route('wash-orders.create') }}" class="btn btn-primary rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Wash
            </a>
            @endcan
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="app-stat-card">
            <p class="app-stat-label">Queued Today</p>
            <p class="text-2xl font-semibold text-info mt-3">{{ $this->statsTodayQueued }}</p>
        </div>
        <div class="app-stat-card">
            <p class="app-stat-label">In Progress</p>
            <p class="text-2xl font-semibold text-warning mt-3">{{ $this->statsInProgress }}</p>
        </div>
        <div class="app-stat-card">
            <p class="app-stat-label">Completed Today</p>
            <p class="text-2xl font-semibold text-success mt-3">{{ $this->statsCompletedToday }}</p>
        </div>
        <div class="app-stat-card">
            <p class="app-stat-label">Bays Available</p>
            <p class="text-2xl font-semibold text-primary mt-3">{{ $this->statsAvailableBays }}</p>
        </div>
    </div>

    @if($view === 'queue')
        <!-- Queue View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Wash Bays Status -->
            <div class="lg:col-span-2">
                <div class="app-panel mb-6">
                    <div class="p-6">
                        <h2 class="card-title text-lg mb-4">Wash Bays</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($this->washBays as $bay)
                                <div class="p-4 rounded-lg border-2 {{ $bay->status instanceof App\Enums\WashBayStatus ? $bay->status->borderClass() : ($bay->status === 'available' ? 'border-success bg-success/5' : ($bay->status === 'occupied' ? 'border-warning bg-warning/5' : 'border-error bg-error/5')) }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-bold">{{ $bay->name }}</h3>
                                        <span class="badge badge-sm {{ $bay->status instanceof App\Enums\WashBayStatus ? $bay->status->badgeClass() : ($bay->status === 'available' ? 'badge-success' : ($bay->status === 'occupied' ? 'badge-warning' : 'badge-error')) }}">
                                            {{ $bay->status instanceof App\Enums\WashBayStatus ? $bay->status->label() : ucfirst($bay->status) }}
                                        </span>
                                    </div>

                                    @if($bay->currentWashOrder)
                                        <div class="text-sm">
                                            <p class="font-medium">{{ $bay->currentWashOrder->vehicle->registration_number }}</p>
                                            <p class="text-base-content/60">{{ ucfirst($bay->currentWashOrder->wash_type) }}</p>
                                            @if($bay->currentWashOrder->started_at)
                                                <p class="text-xs text-base-content/50 mt-1">
                                                    Started {{ $bay->currentWashOrder->started_at->diffForHumans() }}
                                                </p>
                                            @endif

                                            <button
                                                wire:click="completeWash({{ $bay->currentWashOrder->id }})"
                                                class="btn btn-success btn-xs mt-2 w-full"
                                            >
                                                Complete
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-sm text-base-content/50">Ready for next vehicle</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- In Progress -->
                @if($this->inProgress->count() > 0)
                    <div class="app-panel mb-6">
                        <div class="p-6">
                            <h2 class="card-title text-lg mb-4">
                                <span class="w-3 h-3 bg-warning rounded-full animate-pulse"></span>
                                In Progress ({{ $this->inProgress->count() }})
                            </h2>

                            <div class="space-y-3">
                                @foreach($this->inProgress as $order)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <p class="font-bold">{{ $order->vehicle->registration_number }}</p>
                                                <p class="text-sm text-base-content/60">
                                                    {{ ucfirst($order->wash_type) }} • {{ $order->washBay?->name ?? 'No bay' }}
                                                </p>
                                                @if($order->assignedAttendant)
                                                    <p class="text-xs text-base-content/50 mt-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        {{ $order->assignedAttendant->name }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-base-content/60">
                                                {{ $order->started_at?->diffForHumans() }}
                                            </span>
                                            <button wire:click="completeWash({{ $order->id }})" class="btn btn-success btn-sm">
                                                Complete
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Queue -->
            <div>
                <div class="app-panel">
                    <div class="p-6">
                        <h2 class="card-title text-lg mb-4">
                            Queue ({{ $this->queue->count() }})
                        </h2>

                        @if($this->queue->count() > 0)
                            <div class="space-y-2">
                                @foreach($this->queue as $order)
                                    <div class="p-3 border border-gray-200 rounded-lg {{ $order->priority === 'priority' ? 'border-warning bg-yellow-50' : '' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="badge badge-outline badge-sm">{{ $order->queue_position }}</span>
                                                <span class="font-bold">{{ $order->vehicle->registration_number }}</span>
                                            </div>
                                            @if($order->priority === 'priority')
                                                <span class="badge badge-accent badge-xs">PRIORITY</span>
                                            @endif
                                        </div>

                                        <div class="text-sm text-base-content/60 mb-2">
                                            <p>{{ $order->customer->name }}</p>
                                            <p>{{ ucfirst($order->wash_type) }} • {{ $order->source_badge }}</p>
                                        </div>

                                        <div class="flex gap-1">
                                            @if($this->availableBays->isNotEmpty())
                                                <button
                                                    wire:click="openAssignBayModal({{ $order->id }})"
                                                    class="btn btn-primary btn-xs flex-1"
                                                >
                                                    Assign & Start
                                                </button>
                                            @else
                                                <button class="btn btn-disabled btn-xs flex-1" disabled>
                                                    No bay available
                                                </button>
                                            @endif

                                            @if($order->priority !== 'priority')
                                                <button wire:click="prioritize({{ $order->id }})" class="btn btn-ghost btn-xs" title="Prioritize">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                            @endif

                                            <button wire:click="cancel({{ $order->id }})" class="btn btn-ghost btn-xs text-error" title="Cancel">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-base-content/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p>Queue is empty</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- List View -->
        <div class="app-filter-bar">
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search..."
                        class="input input-bordered input-sm"
                    />
                    <select wire:model.live="status" class="select select-bordered select-sm">
                        <option value="">All Statuses</option>
                        <option value="queued">Queued</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select wire:model.live="source" class="select select-bordered select-sm">
                        <option value="">All Sources</option>
                        <option value="walk_in">Walk-in</option>
                        <option value="combo">Combo</option>
                        <option value="appointment">Appointment</option>
                    </select>

                    <select wire:model.live="perPage" class="select select-bordered select-sm">
                        <option value="6">6</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
        </div>

        <div class="app-table-shell">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order #</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Bay</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($washOrders as $index => $order)
                            <tr class="hover">
                                <td>
                                    <div class="text-xs text-gray-900">{{ $washOrders->firstItem() + $index }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('wash-orders.show', $order) }}" class="link link-primary font-mono text-sm">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $order->vehicle->registration_number }}</div>
                                </td>
                                <td>{{ $order->customer->name }}</td>
                                <td>
                                    <span class="badge badge-ghost badge-sm">{{ ucfirst($order->wash_type) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $order->source === 'combo' ? 'accent' : 'ghost' }} badge-sm">
                                        {{ $order->source_badge }}
                                    </span>
                                </td>
                                <td>{{ $order->washBay?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $order->status_color }} badge-sm">
                                        {{ $order->status instanceof \App\Enums\WashOrderStatus ? $order->status->label() : ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $order->created_at->format('d M H:i') }}
                                </td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-xs rounded-lg border border-gray-200 bg-base-100 shadow-none hover:bg-base-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box border border-gray-200 w-48">
                                            <li><a href="{{ route('wash-orders.show', $order) }}">View</a></li>
                                            @if($order->canStart())
                                                <li><button wire:click="startWash({{ $order->id }})">Start</button></li>
                                            @endif
                                            @if($order->canComplete())
                                                <li><button wire:click="completeWash({{ $order->id }})">Complete</button></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-base-content/50">
                                    No wash orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($washOrders->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $washOrders->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Assign Bay Modal -->
    @if($showAssignBayModal)
    <div class="modal modal-open">
        <div class="modal-box app-modal-shell max-w-md">
            <h3 class="font-bold text-lg mb-4">Assign Bay &amp; Start Wash</h3>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-medium">Select Wash Bay *</span></label>
                <select wire:model="selectedBayId" class="select select-bordered">
                    <option value="">— Choose a bay —</option>
                    @foreach($this->availableBays as $bay)
                        <option value="{{ $bay->id }}">{{ $bay->name }} ({{ ucfirst($bay->bay_type instanceof \App\Enums\WashBayType ? $bay->bay_type->label() : $bay->bay_type) }})</option>
                    @endforeach
                </select>
                @error('selectedBayId') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-medium">Assign Attendant <span class="text-base-content/50 font-normal">(optional)</span></span></label>
                <select wire:model="selectedAttendantId" class="select select-bordered">
                    <option value="">— Unassigned —</option>
                    @foreach($this->attendants as $attendant)
                        <option value="{{ $attendant->id }}">{{ $attendant->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="modal-action app-modal-actions">
                <button wire:click="closeAssignBayModal" class="btn btn-ghost">Cancel</button>
                <button wire:click="confirmAssignAndStart" class="btn btn-primary">Start Wash</button>
            </div>
        </div>
        <div class="modal-backdrop app-modal-backdrop" wire:click="closeAssignBayModal"></div>
    </div>
    @endif
</div>
