<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <p class="text-base-content/60">Welcome back! Here's what's happening today.</p>
        </div>

        <!-- Period Selector -->
        <div class="join">
            <button wire:click="setPeriod('today')" class="join-item btn btn-sm {{ $period === 'today' ? 'btn-primary' : 'btn-ghost' }}">Today</button>
            <button wire:click="setPeriod('week')" class="join-item btn btn-sm {{ $period === 'week' ? 'btn-primary' : 'btn-ghost' }}">This Week</button>
            <button wire:click="setPeriod('month')" class="join-item btn btn-sm {{ $period === 'month' ? 'btn-primary' : 'btn-ghost' }}">This Month</button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Work Orders -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Work Orders</p>
                        <p class="text-2xl font-bold">{{ $this->stats['work_orders'] }}</p>
                        <p class="text-xs text-success">{{ $this->stats['completed_work_orders'] }} completed</p>
                    </div>
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wash Orders -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Wash Orders</p>
                        <p class="text-2xl font-bold">{{ $this->stats['wash_orders'] }}</p>
                        <p class="text-xs text-success">{{ $this->stats['completed_wash_orders'] }} completed</p>
                    </div>
                    <div class="w-12 h-12 bg-info/10 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Revenue</p>
                        <p class="text-2xl font-bold">UGX {{ number_format($this->stats['revenue']) }}</p>
                        <p class="text-xs text-base-content/50">Payments received</p>
                    </div>
                    <div class="w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Net Profit</p>
                        <p class="text-2xl font-bold {{ $this->stats['profit'] >= 0 ? 'text-success' : 'text-error' }}">
                            UGX {{ number_format($this->stats['profit']) }}
                        </p>
                        <p class="text-xs text-base-content/50">After UGX {{ number_format($this->stats['expenses']) }} expenses</p>
                    </div>
                    <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bay Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Service Bays -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Service Bays
                </h2>

                <div class="flex gap-2 mb-4">
                    <div class="badge badge-success gap-1">
                        <span class="w-2 h-2 rounded-full bg-success-content"></span>
                        {{ $this->serviceBays->get('available', collect())->count() }} Available
                    </div>
                    <div class="badge badge-warning gap-1">
                        <span class="w-2 h-2 rounded-full bg-warning-content"></span>
                        {{ $this->serviceBays->get('occupied', collect())->count() }} Occupied
                    </div>
                    <div class="badge badge-error gap-1">
                        <span class="w-2 h-2 rounded-full bg-error-content"></span>
                        {{ $this->serviceBays->get('maintenance', collect())->count() }} Maintenance
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($this->serviceBays->flatten() as $bay)
                        <div class="p-3 rounded-lg border {{ $bay->status === 'available' ? 'border-success bg-success/5' : ($bay->status === 'occupied' ? 'border-warning bg-warning/5' : 'border-error bg-error/5') }}">
                            <p class="font-medium text-sm">{{ $bay->name }}</p>
                            @if($bay->currentWorkOrder)
                                <p class="text-xs text-base-content/60 truncate">{{ $bay->currentWorkOrder->vehicle?->registration_number }}</p>
                            @else
                                <p class="text-xs text-base-content/40">{{ ucfirst($bay->status) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Wash Bays -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Wash Bays
                </h2>

                <div class="flex gap-2 mb-4">
                    <div class="badge badge-success gap-1">
                        <span class="w-2 h-2 rounded-full bg-success-content"></span>
                        {{ $this->washBays->get('available', collect())->count() }} Available
                    </div>
                    <div class="badge badge-warning gap-1">
                        <span class="w-2 h-2 rounded-full bg-warning-content"></span>
                        {{ $this->washBays->get('occupied', collect())->count() }} Occupied
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($this->washBays->flatten() as $bay)
                        <div class="p-3 rounded-lg border {{ $bay->status === \App\Enums\WashBayStatus::Available ? 'border-success bg-success/5' : ($bay->status === \App\Enums\WashBayStatus::Occupied ? 'border-warning bg-warning/5' : 'border-error bg-error/5') }}">
                            <p class="font-medium text-sm">{{ $bay->name }}</p>
                            @if($bay->currentWashOrder)
                                <p class="text-xs text-base-content/60 truncate">{{ $bay->currentWashOrder->vehicle?->registration_number }}</p>
                            @else
                                <p class="text-xs text-base-content/40">{{ $bay->status->label() }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Wash Queue Preview -->
                @if($this->washQueue->count() > 0)
                    <div class="divider text-xs">Queue ({{ $this->washQueue->count() }} waiting)</div>
                    <div class="space-y-2">
                        @foreach($this->washQueue->take(3) as $order)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-sm badge-outline">{{ $order->queue_position }}</span>
                                    <span>{{ $order->vehicle?->registration_number ?? 'N/A' }}</span>
                                </div>
                                <span class="badge badge-sm">{{ ucfirst($order->wash_type) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Work & Appointments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Active Work Orders -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Active Work Orders</h2>
                    <a href="{{ route('work-orders.index') }}" class="btn btn-ghost btn-sm">View All</a>
                </div>

                @if($this->activeWorkOrders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Technician</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->activeWorkOrders as $order)
                                    <tr class="hover">
                                        <td>
                                            <a href="{{ route('work-orders.show', $order) }}" class="link link-primary font-mono text-sm">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="font-medium">{{ $order->vehicle?->registration_number ?? 'N/A' }}</div>
                                            <div class="text-xs text-base-content/60">{{ $order->customer?->name }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $order->status_color }} badge-sm">
                                                {{ str_replace('_', ' ', ucfirst($order->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-sm">{{ $order->assignedTechnician?->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p>No active work orders</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Today's Appointments</h2>
                    <a href="{{ route('appointments.index') }}" class="btn btn-ghost btn-sm">View All</a>
                </div>

                @if($this->todayAppointments->count() > 0)
                    <div class="space-y-3">
                        @foreach($this->todayAppointments as $appointment)
                            <div class="flex items-center gap-4 p-3 rounded-lg bg-base-200/50">
                                <div class="text-center">
                                    <div class="text-lg font-bold">{{ $appointment->scheduled_time->format('H:i') }}</div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium">{{ $appointment->customer?->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-base-content/60">
                                        {{ $appointment->vehicle?->registration_number ?? 'N/A' }} • {{ $appointment->type_display }}
                                    </div>
                                </div>
                                <span class="badge badge-{{ $appointment->status_color }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p>No appointments today</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Unpaid Invoices -->
    @can('view invoices')
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Unpaid Invoices
                </h2>
                <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="btn btn-ghost btn-sm">View All</a>
            </div>

            @if($this->unpaidInvoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->unpaidInvoices as $invoice)
                                <tr class="hover">
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="link link-primary font-mono text-sm">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $invoice->customer?->name ?? 'N/A' }}</td>
                                    <td class="font-medium">UGX {{ number_format($invoice->balance_due) }}</td>
                                    <td class="{{ $invoice->isOverdue() ? 'text-error' : '' }}">
                                        {{ $invoice->due_date->format('d M Y') }}
                                        @if($invoice->isOverdue())
                                            <span class="text-xs">({{ $invoice->days_overdue }}d overdue)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $invoice->status_color }} badge-sm">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6 text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>All invoices are paid!</p>
                </div>
            @endif
        </div>
    </div>
    @endcan
</div>
