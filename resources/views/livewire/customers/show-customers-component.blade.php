<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('customers.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-neutral text-neutral-content rounded-full w-14">
                        <span class="text-xl">{{ substr($customer->name, 0, 2) }}</span>
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $customer->name }}</h1>
                    <p class="text-base-content/60">{{ $customer->phone }}</p>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('work-orders.create', ['customer' => $customer->id]) }}" class="btn btn-primary btn-sm">New Work Order</a>
            @can('edit customers')
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost btn-sm">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Vehicles -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-lg">Vehicles ({{ $customer->vehicles->count() }})</h2>
                        <a href="{{ route('vehicles.create', ['customer' => $customer->id]) }}" class="btn btn-ghost btn-xs">+ Add</a>
                    </div>
                    @if($customer->vehicles->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($customer->vehicles as $vehicle)
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="p-3 border border-base-300 rounded-lg hover:border-primary transition-colors">
                                    <p class="font-bold">{{ $vehicle->registration_number }}</p>
                                    <p class="text-sm text-base-content/60">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-base-content/50 text-center py-4">No vehicles registered</p>
                    @endif
                </div>
            </div>

            <!-- Recent Work Orders -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Recent Work Orders</h2>
                    @if($customer->workOrders->count() > 0)
                        <div class="space-y-2">
                            @foreach($customer->workOrders as $order)
                                <a href="{{ route('work-orders.show', $order) }}" class="flex items-center justify-between p-3 hover:bg-base-200 rounded-lg">
                                    <div>
                                        <span class="font-mono text-sm">{{ $order->order_number }}</span>
                                        <span class="text-base-content/60 text-sm ml-2">{{ $order->vehicle->registration_number }}</span>
                                    </div>
                                    <span class="badge badge-{{ $order->status_color }} badge-sm">{{ ucfirst($order->status) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-base-content/50 text-center py-4">No work orders yet</p>
                    @endif
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Recent Invoices</h2>
                    @if($customer->invoices->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->invoices as $invoice)
                                        <tr class="hover">
                                            <td><a href="{{ route('invoices.show', $invoice) }}" class="link link-primary font-mono text-sm">{{ $invoice->invoice_number }}</a></td>
                                            <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                            <td>UGX {{ number_format($invoice->total) }}</td>
                                            <td><span class="badge badge-{{ $invoice->status_color }} badge-sm">{{ ucfirst($invoice->status) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-base-content/50 text-center py-4">No invoices yet</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Contact Info -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Contact Info</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <span>{{ $customer->phone }}</span>
                        </div>
                        @if($customer->email)
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <span>{{ $customer->email }}</span>
                            </div>
                        @endif
                        @if($customer->address)
                            <div class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>{{ $customer->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Summary</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Total Spent</span>
                            <span class="font-bold">UGX {{ number_format($customer->invoices->sum('total')) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Work Orders</span>
                            <span>{{ $customer->workOrders->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Wash Orders</span>
                            <span>{{ $customer->washOrders->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Loyalty Points</span>
                            <span class="badge badge-primary">{{ number_format($customer->loyalty_points) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Customer Since</span>
                            <span>{{ $customer->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($customer->notes)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-2">Notes</h2>
                        <p class="text-sm text-base-content/70">{{ $customer->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
