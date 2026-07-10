<div class="space-y-8">
    <div class="app-page-header">
        <div>
            <p class="app-kicker">Operations overview</p>
            <h1 class="app-title">Dashboard</h1>
            <p class="app-subtitle">A quieter overview of service activity, wash flow, revenue, and branch workload for the selected period.</p>
        </div>

        <div class="app-segmented self-start lg:self-auto">
            <button wire:click="setPeriod('today')" class="app-segment-button {{ $period === 'today' ? 'is-active' : '' }}">Today</button>
            <button wire:click="setPeriod('week')" class="app-segment-button {{ $period === 'week' ? 'is-active' : '' }}">This Week</button>
            <button wire:click="setPeriod('month')" class="app-segment-button {{ $period === 'month' ? 'is-active' : '' }}">This Month</button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="app-stat-card">
            <p class="app-stat-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Work orders
            </p>
            <p class="app-stat-value">{{ $this->stats['work_orders'] }}</p>
            <p class="app-stat-meta"><span class="font-semibold text-success">{{ $this->stats['completed_work_orders'] }}</span> completed in this window</p>
        </div>

        <div class="app-stat-card">
            <p class="app-stat-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                Wash orders
            </p>
            <p class="app-stat-value">{{ $this->stats['wash_orders'] }}</p>
            <p class="app-stat-meta"><span class="font-semibold text-success">{{ $this->stats['completed_wash_orders'] }}</span> completed in this window</p>
        </div>

        <div class="app-stat-card">
            <p class="app-stat-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Revenue collected
            </p>
            <p class="app-stat-value">UGX {{ number_format($this->stats['revenue']) }}</p>
            <p class="app-stat-meta">Confirmed payments received</p>
        </div>

        <div class="app-stat-card">
            <p class="app-stat-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Net profit
            </p>
            <p class="app-stat-value {{ $this->stats['profit'] >= 0 ? 'text-success' : 'text-error' }}">UGX {{ number_format($this->stats['profit']) }}</p>
            <p class="app-stat-meta">After UGX {{ number_format($this->stats['expenses']) }} in approved expenses</p>
        </div>
    </div>

    <!-- Bay Status -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Service Bays -->
        <div class="app-panel">
            <div class="p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="app-eyebrow">Live floor state</p>
                        <div class="mt-2 flex items-center gap-2.5">
                            <h2 class="text-xl font-semibold tracking-[-0.03em] text-base-content">Service bays</h2>
                            <span class="live-dot"><span class="live-dot__pulse"></span>live</span>
                        </div>
                    </div>
                    <div class="app-stat-icon h-11 w-11">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    </div>
                </div>

                <div class="mb-5 flex flex-wrap gap-2">
                    <div class="app-badge-soft gap-2 text-success">
                        <span class="w-2 h-2 rounded-full bg-success-content"></span>
                        {{ $this->serviceBays->get('available', collect())->count() }} Available
                    </div>
                    <div class="app-badge-soft gap-2 text-warning">
                        <span class="w-2 h-2 rounded-full bg-warning-content"></span>
                        {{ $this->serviceBays->get('occupied', collect())->count() }} Occupied
                    </div>
                    <div class="app-badge-soft gap-2 text-error">
                        <span class="w-2 h-2 rounded-full bg-error-content"></span>
                        {{ $this->serviceBays->get('maintenance', collect())->count() }} Maintenance
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($this->serviceBays->flatten() as $bay)
                        <div class="app-mini-card {{ $bay->status === 'available' ? 'border-success/35 bg-success/5' : ($bay->status === 'occupied' ? 'border-warning/35 bg-warning/5' : 'border-error/35 bg-error/5') }}">
                            <p class="font-medium text-sm text-base-content">{{ $bay->name }}</p>
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
        <div class="app-panel">
            <div class="p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="app-eyebrow">Queue health</p>
                        <div class="mt-2 flex items-center gap-2.5">
                            <h2 class="text-xl font-semibold tracking-[-0.03em] text-base-content">Wash bays</h2>
                            <span class="live-dot"><span class="live-dot__pulse"></span>live</span>
                        </div>
                    </div>
                    <div class="app-stat-icon h-11 w-11">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    </div>
                </div>

                <div class="mb-5 flex flex-wrap gap-2">
                    <div class="app-badge-soft gap-2 text-success">
                        <span class="w-2 h-2 rounded-full bg-success-content"></span>
                        {{ $this->washBays->get('available', collect())->count() }} Available
                    </div>
                    <div class="app-badge-soft gap-2 text-warning">
                        <span class="w-2 h-2 rounded-full bg-warning-content"></span>
                        {{ $this->washBays->get('occupied', collect())->count() }} Occupied
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($this->washBays->flatten() as $bay)
                        <div class="app-mini-card {{ $bay->status === \App\Domains\Operations\Enums\WashBayStatus::Available ? 'border-success/35 bg-success/5' : ($bay->status === \App\Domains\Operations\Enums\WashBayStatus::Occupied ? 'border-warning/35 bg-warning/5' : 'border-error/35 bg-error/5') }}">
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
                    <div class="divider text-xs text-base-content/45">Queue ({{ $this->washQueue->count() }} waiting)</div>
                    <div class="space-y-2">
                        @foreach($this->washQueue->take(3) as $order)
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-sm border-0 bg-base-100">{{ $order->queue_position }}</span>
                                    <span>{{ $order->vehicle?->registration_number ?? 'N/A' }}</span>
                                </div>
                                <span class="badge badge-sm border-0 bg-base-100">{{ ucfirst($order->wash_type) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Work & Appointments -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Active Work Orders -->
        <div class="app-table-shell">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5">
                <div>
                    <p class="app-eyebrow">Workshop activity</p>
                    <div class="mt-2 flex items-center gap-2.5">
                        <h2 class="text-xl font-semibold tracking-[-0.03em] text-base-content">Active work orders</h2>
                        <span class="live-dot"><span class="live-dot__pulse"></span>live</span>
                    </div>
                </div>
                <a href="{{ route('work-orders.index') }}" class="btn rounded-lg border border-gray-200 bg-base-100 px-4 shadow-none hover:bg-base-200">View All</a>
            </div>

            <div class="p-6">
                @if($this->activeWorkOrders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Order</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Technician</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->activeWorkOrders as $index => $order)
                                    <tr class="hover">
                                        <td>{{ $index + 1 }}</td>
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
                    <div class="app-empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p>No active work orders</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="app-panel">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5">
                <div>
                    <p class="app-eyebrow">Customer schedule</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-base-content">Today's appointments</h2>
                </div>
                <a href="{{ route('appointments.index') }}" class="btn rounded-lg border border-gray-200 bg-base-100 px-4 shadow-none hover:bg-base-200">View All</a>
            </div>

            <div class="p-6">

                @if($this->todayAppointments->count() > 0)
                    <div class="space-y-3">
                        @foreach($this->todayAppointments as $appointment)
                            <div class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
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
                    <div class="app-empty-state">
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
    @can('view_invoices')
    <div class="app-table-shell">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5">
            <div>
                <p class="app-eyebrow">Collections</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-base-content">Unpaid invoices</h2>
            </div>
            <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="btn rounded-lg border border-gray-200 bg-base-100 px-4 shadow-none hover:bg-base-200">View All</a>
        </div>

        <div class="p-6">

            @if($this->unpaidInvoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->unpaidInvoices as $index => $invoice)
                                <tr class="hover">
                                    <td>{{ $index + 1 }}</td>
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
                <div class="app-empty-state text-success">
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
