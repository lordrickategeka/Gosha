<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Work Orders</h1>
            <p class="text-base-content/60">Manage service and repair jobs</p>
        </div>

        @can('create work orders')
        <a href="{{ route('work-orders.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Work Order
        </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="form-control">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search orders, vehicles, customers..."
                        class="input input-bordered input-sm w-full"
                    />
                </div>

                <!-- Status Filter -->
                <div class="form-control">
                    <select wire:model.live="status" class="select select-bordered select-sm w-full">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="quality_check">Quality Check</option>
                        <option value="ready">Ready</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="form-control">
                    <select wire:model.live="type" class="select select-bordered select-sm w-full">
                        <option value="">All Types</option>
                        <option value="service">Service</option>
                        <option value="repair">Repair</option>
                        <option value="diagnostics">Diagnostics</option>
                        <option value="bodywork">Bodywork</option>
                        <option value="electrical">Electrical</option>
                        <option value="ac">A/C</option>
                        <option value="tyres">Tyres</option>
                    </select>
                </div>

                <!-- Technician Filter -->
                <div class="form-control">
                    <select wire:model.live="technician" class="select select-bordered select-sm w-full">
                        <option value="">All Technicians</option>
                        @foreach($this->technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="form-control">
                    <button wire:click="clearFilters" class="btn btn-ghost btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Open</div>
            <div class="stat-value text-lg text-info">{{ $workOrders->where('status', 'open')->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">In Progress</div>
            <div class="stat-value text-lg text-warning">{{ $workOrders->where('status', 'in_progress')->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Ready</div>
            <div class="stat-value text-lg text-success">{{ $workOrders->where('status', 'ready')->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Today</div>
            <div class="stat-value text-lg">{{ $workOrders->where('created_at', '>=', today())->count() }}</div>
        </div>
    </div>

    <!-- Work Orders Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No.</th>
                        <th>Vehicle</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Bay</th>
                        <th>Technician</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workOrders as $iteration => $order)
                        <tr class="hover">
                            <td>{{ $iteration + 1 }}</td>
                            <td>
                                <a href="{{ route('work-orders.show', $order) }}" class="link link-primary font-mono text-sm font-medium">
                                    {{ $order->order_number }}
                                </a>
                                @if($order->is_combo)
                                    <span class="badge badge-accent badge-xs ml-1">COMBO</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-medium">{{ $order->vehicle->registration_number }}</div>
                                <div class="text-xs text-base-content/60">{{ $order->vehicle->make }} {{ $order->vehicle->model }}</div>
                            </td>
                            <td>
                                <div>{{ $order->customer->name }}</div>
                                <div class="text-xs text-base-content/60">{{ $order->customer->phone }}</div>
                            </td>
                            <td>
                                <span class="badge badge-ghost badge-sm">{{ ucfirst($order->type) }}</span>
                            </td>
                            <td>
                                @if($order->serviceBay)
                                    <span class="text-sm">{{ $order->serviceBay->name }}</span>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>
                                @if($order->assignedTechnician)
                                    <div class="flex items-center gap-2">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-6">
                                                <span class="text-xs">{{ substr($order->assignedTechnician->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $order->assignedTechnician->name }}</span>
                                    </div>
                                @else
                                    <span class="text-base-content/40">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $order->status_color }} badge-sm">
                                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                </span>
                                @if($order->priority === 'urgent')
                                    <span class="badge badge-error badge-xs ml-1">URGENT</span>
                                @endif
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
                                        <li><a href="{{ route('work-orders.show', $order) }}">View Details</a></li>
                                        @can('edit work orders')
                                            <li><a href="{{ route('work-orders.edit', $order) }}">Edit</a></li>
                                        @endcan
                                        @can('change work order status')
                                            @if($order->canStart())
                                                <li><button wire:click="startWorkOrder({{ $order->id }})">Start Work</button></li>
                                            @endif
                                            @if($order->canComplete())
                                                <li><button wire:click="markReady({{ $order->id }})">Mark Ready</button></li>
                                            @endif
                                            @if($order->canDeliver())
                                                <li><button wire:click="deliver({{ $order->id }})">Deliver</button></li>
                                            @endif
                                        @endcan
                                        @can('create invoices')
                                            @if($order->status === 'ready' && !$order->invoice)
                                                <li><a href="{{ route('invoices.create', ['work_order' => $order->id]) }}">Create Invoice</a></li>
                                            @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-base-content/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p>No work orders found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workOrders->hasPages())
            <div class="p-4 border-t border-base-200">
                {{ $workOrders->links() }}
            </div>
        @endif
    </div>
</div>
