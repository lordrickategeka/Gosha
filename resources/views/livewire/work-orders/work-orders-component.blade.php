<div class="space-y-6">
    <div class="app-page-header">
        <div>
            <p class="app-kicker">Workshop operations</p>
            <h1 class="app-title">Work Orders</h1>
            <p class="app-subtitle">Track active service jobs, filter operational load, and move vehicles cleanly through the workshop pipeline.</p>
        </div>

        @can('create_work_orders')
        <a href="{{ route('work-orders.create') }}" class="btn btn-primary rounded-lg px-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Work Order
        </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="app-filter-bar">
        <div class="mb-4 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="app-eyebrow">Refine the queue</p>
                <h2 class="mt-1 text-lg font-semibold tracking-[-0.02em] text-base-content">Filters</h2>
            </div>
            <button wire:click="clearFilters" class="btn rounded-lg border border-gray-200 bg-base-100 px-4 text-base-content shadow-none hover:bg-base-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Clear filters
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
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

                <div class="form-control">
                    <select wire:model.live="perPage" class="select select-bordered select-sm w-full">
                        <option value="6">6 per page</option>
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
        </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="app-stat-card">
            <div class="app-stat-label">Open</div>
            <div class="text-2xl font-semibold text-info mt-3">{{ $workOrders->where('status', 'open')->count() }}</div>
        </div>
        <div class="app-stat-card">
            <div class="app-stat-label">In progress</div>
            <div class="text-2xl font-semibold text-warning mt-3">{{ $workOrders->where('status', 'in_progress')->count() }}</div>
        </div>
        <div class="app-stat-card">
            <div class="app-stat-label">Ready</div>
            <div class="text-2xl font-semibold text-success mt-3">{{ $workOrders->where('status', 'ready')->count() }}</div>
        </div>
        <div class="app-stat-card">
            <div class="app-stat-label">Created today</div>
            <div class="text-2xl font-semibold mt-3">{{ $workOrders->where('created_at', '>=', today())->count() }}</div>
        </div>
    </div>

    <!-- Work Orders Table -->
    <div class="app-table-shell">
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="app-eyebrow">Operational queue</p>
                <h2 class="mt-1 text-xl font-semibold tracking-[-0.03em] text-base-content">Current work orders</h2>
            </div>
            <p class="text-sm text-gray-500">{{ $workOrders->total() }} total records</p>
        </div>

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
                                <a href="{{ route('work-orders.show', $order) }}" class="font-mono text-sm font-medium text-primary hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                @if($order->is_combo)
                                    <span class="badge border-0 bg-accent/15 text-accent badge-xs ml-1">COMBO</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-medium">{{ $order->vehicle?->registration_number ?? 'N/A' }}</div>
                                <div class="text-xs text-base-content/60">{{ trim(($order->vehicle?->make ?? '') . ' ' . ($order->vehicle?->model ?? '')) ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $order->customer?->name ?? 'Walk-in / Unknown' }}</div>
                                <div class="text-xs text-base-content/60">{{ $order->customer?->phone ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge border-0 bg-base-200 text-base-content/70 badge-sm">{{ ucfirst($order->type) }}</span>
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
                                    <label tabindex="0" class="btn btn-xs rounded-lg border border-gray-200 bg-base-100 shadow-none hover:bg-base-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu mt-2 w-48 rounded-lg border border-gray-200 bg-base-100 p-2 shadow-lg">
                                        <li><a href="{{ route('work-orders.show', $order) }}">View Details</a></li>
                                        @can('edit_work_orders')
                                            <li><a href="{{ route('work-orders.edit', $order) }}">Edit</a></li>
                                        @endcan
                                        @can('change_work_order_status')
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
                                        @can('view_invoices')
                                            @if($order->invoice)
                                                <li><a href="{{ route('invoices.show', $order->invoice) }}">View Invoice</a></li>
                                            @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8">
                                <div class="app-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p>No work orders found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workOrders->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $workOrders->links() }}
            </div>
        @endif
    </div>
</div>
