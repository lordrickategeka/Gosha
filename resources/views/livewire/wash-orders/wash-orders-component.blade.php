<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Wash Bay</h1>
            <p class="text-base-content/60">Queue management and wash orders</p>
        </div>

        <div class="flex gap-2">
            <div class="join">
                <button wire:click="$set('view', 'queue')" class="join-item btn btn-sm {{ $view === 'queue' ? 'btn-primary' : 'btn-ghost' }}">
                    Queue View
                </button>
                <button wire:click="$set('view', 'list')" class="join-item btn btn-sm {{ $view === 'list' ? 'btn-primary' : 'btn-ghost' }}">
                    List View
                </button>
            </div>

            @can('create wash orders')
            <a href="{{ route('wash-orders.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Wash
            </a>
            @endcan
        </div>
    </div>

    @if($view === 'queue')
        <!-- Queue View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Wash Bays Status -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-sm mb-6">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Wash Bays</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($this->washBays as $bay)
                                <div class="p-4 rounded-lg border-2 {{ $bay->status === 'available' ? 'border-success bg-success/5' : ($bay->status === 'occupied' ? 'border-warning bg-warning/5' : 'border-error bg-error/5') }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-bold">{{ $bay->name }}</h3>
                                        <span class="badge badge-sm {{ $bay->status === 'available' ? 'badge-success' : ($bay->status === 'occupied' ? 'badge-warning' : 'badge-error') }}">
                                            {{ ucfirst($bay->status) }}
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
                    <div class="card bg-base-100 shadow-sm mb-6">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <span class="w-3 h-3 bg-warning rounded-full animate-pulse"></span>
                                In Progress ({{ $this->inProgress->count() }})
                            </h2>

                            <div class="space-y-3">
                                @foreach($this->inProgress as $order)
                                    <div class="flex items-center justify-between p-3 bg-warning/10 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <p class="font-bold">{{ $order->vehicle->registration_number }}</p>
                                                <p class="text-sm text-base-content/60">
                                                    {{ ucfirst($order->wash_type) }} • {{ $order->washBay?->name ?? 'No bay' }}
                                                </p>
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
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            Queue ({{ $this->queue->count() }})
                        </h2>

                        @if($this->queue->count() > 0)
                            <div class="space-y-2">
                                @foreach($this->queue as $order)
                                    <div class="p-3 border border-base-300 rounded-lg {{ $order->priority === 'priority' ? 'border-accent bg-accent/5' : '' }}">
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
                                            @php
                                                $availableBay = $this->washBays->firstWhere('status', 'available');
                                            @endphp

                                            @if($availableBay)
                                                <button
                                                    wire:click="startWash({{ $order->id }}, {{ $availableBay->id }})"
                                                    class="btn btn-primary btn-xs flex-1"
                                                >
                                                    Start ({{ $availableBay->name }})
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
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body p-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
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
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Bay</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($washOrders as $order)
                            <tr class="hover">
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
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $order->created_at->format('d M H:i') }}
                                </td>
                                <td>
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
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
                                <td colspan="9" class="text-center py-8 text-base-content/50">
                                    No wash orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($washOrders->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $washOrders->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
