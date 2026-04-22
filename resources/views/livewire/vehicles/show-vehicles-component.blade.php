<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('vehicles.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold font-mono">{{ $vehicle->registration_number }}</h1>
                <p class="text-base-content/60">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('work-orders.create', ['customer' => $vehicle->customer_id, 'vehicle' => $vehicle->id]) }}" class="btn btn-primary btn-sm">New Work Order</a>
            @can('edit vehicles')
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-ghost btn-sm">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Service History -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Work Order History</h2>
                    @if($vehicle->workOrders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Order</th><th>Type</th><th>Date</th><th>Status</th><th>Total</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicle->workOrders as $order)
                                        <tr class="hover">
                                            <td><a href="{{ route('work-orders.show', $order) }}" class="link link-primary font-mono">{{ $order->order_number }}</a></td>
                                            <td><span class="badge badge-ghost badge-sm">{{ ucfirst($order->type) }}</span></td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td><span class="badge badge-{{ $order->status_color }} badge-sm">{{ ucfirst($order->status) }}</span></td>
                                            <td>UGX {{ number_format($order->subtotal) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-4 text-base-content/50">No work orders yet</p>
                    @endif
                </div>
            </div>

            <!-- Wash History -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Wash History</h2>
                    @if($vehicle->washOrders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Order</th><th>Type</th><th>Date</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicle->washOrders as $order)
                                        <tr class="hover">
                                            <td><a href="{{ route('wash-orders.show', $order) }}" class="link link-primary font-mono">{{ $order->order_number }}</a></td>
                                            <td>{{ ucfirst($order->wash_type) }}</td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td><span class="badge badge-{{ $order->status_color }} badge-sm">{{ ucfirst($order->status) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-4 text-base-content/50">No wash orders yet</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Vehicle Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Vehicle Details</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-base-content/60">Make</span><span>{{ $vehicle->make ?? '-' }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Model</span><span>{{ $vehicle->model ?? '-' }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Year</span><span>{{ $vehicle->year ?? '-' }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Color</span><span>{{ $vehicle->color ?? '-' }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Fuel Type</span><span>{{ ucfirst($vehicle->fuel_type ?? '-') }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Transmission</span><span>{{ ucfirst($vehicle->transmission ?? '-') }}</span></div>
                        @if($vehicle->vin)
                            <div class="flex justify-between"><span class="text-base-content/60">VIN</span><span class="font-mono text-xs">{{ $vehicle->vin }}</span></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Owner -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Owner</h2>
                    <a href="{{ route('customers.show', $vehicle->customer) }}" class="flex items-center gap-3 p-3 bg-base-200 rounded-lg hover:bg-base-300">
                        <div class="avatar placeholder">
                            <div class="bg-neutral text-neutral-content rounded-full w-10">
                                <span>{{ substr($vehicle->customer->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium">{{ $vehicle->customer->name }}</p>
                            <p class="text-sm text-base-content/60">{{ $vehicle->customer->phone }}</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Summary</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-base-content/60">Work Orders</span><span class="font-medium">{{ $vehicle->workOrders->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Washes</span><span class="font-medium">{{ $vehicle->washOrders->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Last Service</span><span>{{ $vehicle->workOrders->first()?->created_at->format('d M Y') ?? 'Never' }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
