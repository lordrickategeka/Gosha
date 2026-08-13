<div class="gh-page">
    <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:20px; flex-wrap:wrap;">
        <div>
            <div class="gh-eyebrow">Operations overview</div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:4px;">Dashboard</div>
            <p class="gh-muted" style="font-size:12.5px; max-width:560px; margin:6px 0 0;">A quieter overview of service activity, wash flow, revenue, and branch workload for the selected period.</p>
        </div>

        <div class="gh-segmented">
            <button wire:click="setPeriod('today')" class="{{ $period === 'today' ? 'is-active' : '' }}">Today</button>
            <button wire:click="setPeriod('week')" class="{{ $period === 'week' ? 'is-active' : '' }}">This Week</button>
            <button wire:click="setPeriod('month')" class="{{ $period === 'month' ? 'is-active' : '' }}">This Month</button>
        </div>
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Work orders</span>
            <span class="gh-stat__value">{{ $this->stats['work_orders'] }}</span>
            <span class="gh-stat__sub"><b style="color:var(--gh-success);">{{ $this->stats['completed_work_orders'] }}</b> completed in this window</span>
        </div>

        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Wash orders</span>
            <span class="gh-stat__value">{{ $this->stats['wash_orders'] }}</span>
            <span class="gh-stat__sub"><b style="color:var(--gh-success);">{{ $this->stats['completed_wash_orders'] }}</b> completed in this window</span>
        </div>

        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Revenue collected</span>
            <span class="gh-stat__value">UGX {{ number_format($this->stats['revenue']) }}</span>
            <span class="gh-stat__sub">Confirmed payments received</span>
        </div>

        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Net profit</span>
            <span class="gh-stat__value {{ $this->stats['profit'] >= 0 ? '' : 'gh-stat__value--neg' }}">UGX {{ number_format($this->stats['profit']) }}</span>
            <span class="gh-stat__sub">After UGX {{ number_format($this->stats['expenses']) }} in approved expenses</span>
        </div>
    </div>

    <div class="gh-grid-2">
        <!-- Service Bays -->
        <div class="gh-card gh-card--pad gh-stack" style="gap:14px;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:14px;">
                <div>
                    <div class="gh-eyebrow">Live floor state</div>
                    <div class="gh-card__title" style="margin-top:4px;">Service bays</div>
                </div>
                <div class="gh-stat__icon"><x-icon name="branches" class="gh-icon" /></div>
            </div>

            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <span class="gh-badge gh-badge--success">{{ $this->serviceBays->get('available', collect())->count() }} Available</span>
                <span class="gh-badge gh-badge--warning">{{ $this->serviceBays->get('occupied', collect())->count() }} Occupied</span>
                <span class="gh-badge gh-badge--error">{{ $this->serviceBays->get('maintenance', collect())->count() }} Maintenance</span>
            </div>

            <div class="gh-grid-3">
                @foreach($this->serviceBays->flatten() as $bay)
                    <div class="gh-bay {{ $bay->status === 'occupied' ? 'is-occupied' : ($bay->status === 'maintenance' ? 'is-maintenance' : '') }}">
                        <span class="gh-bay__name">{{ $bay->name }}</span>
                        @if($bay->currentWorkOrder)
                            <span class="gh-muted" style="font-size:11px;">{{ $bay->currentWorkOrder->vehicle?->registration_number }}</span>
                        @else
                            <span class="gh-bay__state">{{ ucfirst($bay->status) }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Wash Bays -->
        <div class="gh-card gh-card--pad gh-stack" style="gap:14px;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:14px;">
                <div>
                    <div class="gh-eyebrow">Queue health</div>
                    <div class="gh-card__title" style="margin-top:4px;">Wash bays</div>
                </div>
                <div class="gh-stat__icon"><x-icon name="washBay" class="gh-icon" /></div>
            </div>

            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <span class="gh-badge gh-badge--success">{{ $this->washBays->get('available', collect())->count() }} Available</span>
                <span class="gh-badge gh-badge--warning">{{ $this->washBays->get('occupied', collect())->count() }} Occupied</span>
            </div>

            <div class="gh-grid-3">
                @foreach($this->washBays->flatten() as $bay)
                    <div class="gh-bay {{ $bay->status === \App\Domains\Operations\Enums\WashBayStatus::Occupied ? 'is-occupied' : '' }}">
                        <span class="gh-bay__name">{{ $bay->name }}</span>
                        @if($bay->currentWashOrder)
                            <span class="gh-muted" style="font-size:11px;">{{ $bay->currentWashOrder->vehicle?->registration_number }}</span>
                        @else
                            <span class="gh-bay__state">{{ $bay->status->label() }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($this->washQueue->count() > 0)
                <div class="gh-stack" style="gap:8px; border-top:1px solid var(--gh-hairline); padding-top:12px;">
                    <span class="gh-eyebrow">Queue ({{ $this->washQueue->count() }} waiting)</span>
                    @foreach($this->washQueue->take(3) as $order)
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 11px; border-radius:var(--gh-radius); background:var(--gh-base-200); font-size:12.5px;">
                            <span style="display:flex; align-items:center; gap:8px;">
                                <span class="gh-badge">{{ $order->queue_position }}</span>
                                {{ $order->vehicle?->registration_number ?? 'N/A' }}
                            </span>
                            <span class="gh-badge">{{ ucfirst($order->wash_type) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="gh-grid-2">
        <!-- Active Work Orders -->
        <div class="gh-card gh-card--flush">
            <div class="gh-card__head">
                <div>
                    <div class="gh-eyebrow">Workshop activity</div>
                    <div class="gh-card__title" style="margin-top:4px;">Active work orders</div>
                </div>
                <a href="{{ route('work-orders.index') }}" class="gh-btn gh-btn--sm">View all</a>
            </div>

            @if($this->activeWorkOrders->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead>
                            <tr><th class="is-index">#</th><th>Order</th><th>Vehicle</th><th>Status</th><th>Technician</th></tr>
                        </thead>
                        <tbody>
                            @foreach($this->activeWorkOrders as $index => $order)
                                <tr data-href="{{ route('work-orders.show', $order) }}">
                                    <td class="is-index">{{ $index + 1 }}</td>
                                    <td class="is-ref"><a href="{{ route('work-orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                    <td>
                                        <div class="gh-cell-stack">
                                            <b>{{ $order->vehicle?->registration_number ?? 'N/A' }}</b>
                                            <span>{{ $order->customer?->name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="gh-badge gh-badge--{{ $order->status_color }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span></td>
                                    <td>{{ $order->assignedTechnician?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="gh-page" style="text-align:center; color:var(--gh-ink-faint); font-size:12.5px;">No active work orders</div>
            @endif
        </div>

        <!-- Today's Appointments -->
        <div class="gh-card gh-card--pad gh-stack" style="gap:14px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
                <div>
                    <div class="gh-eyebrow">Customer schedule</div>
                    <div class="gh-card__title" style="margin-top:4px;">Today's appointments</div>
                </div>
                <a href="{{ route('appointments.index') }}" class="gh-btn gh-btn--sm">View all</a>
            </div>

            @if($this->todayAppointments->count() > 0)
                <div class="gh-stack" style="gap:8px;">
                    @foreach($this->todayAppointments as $appointment)
                        <div style="display:flex; align-items:center; gap:14px; padding:11px; border:1px solid var(--gh-base-300); border-radius:var(--gh-radius-md); background:var(--gh-surface-raised);">
                            <b style="font-size:14px;">{{ $appointment->scheduled_time->format('H:i') }}</b>
                            <div style="flex:1; min-width:0;" class="gh-cell-stack">
                                <b>{{ $appointment->customer?->name ?? 'N/A' }}</b>
                                <span>{{ $appointment->vehicle?->registration_number ?? 'N/A' }} · {{ $appointment->type_display }}</span>
                            </div>
                            <span class="gh-badge gh-badge--{{ $appointment->status_color }}">{{ ucfirst($appointment->status) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center; color:var(--gh-ink-faint); font-size:12.5px; padding:20px 0;">No appointments today</div>
            @endif
        </div>
    </div>

    <!-- Unpaid Invoices -->
    @can('view_invoices')
        <div class="gh-card gh-card--flush">
            <div class="gh-card__head">
                <div>
                    <div class="gh-eyebrow">Collections</div>
                    <div class="gh-card__title" style="margin-top:4px;">Unpaid invoices</div>
                </div>
                <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="gh-btn gh-btn--sm">View all</a>
            </div>

            @if($this->unpaidInvoices->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead>
                            <tr><th class="is-index">#</th><th>Invoice</th><th>Customer</th><th style="text-align:right;">Amount</th><th>Due date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($this->unpaidInvoices as $index => $invoice)
                                <tr data-href="{{ route('invoices.show', $invoice) }}">
                                    <td class="is-index">{{ $index + 1 }}</td>
                                    <td class="is-ref"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                    <td>{{ $invoice->customer?->name ?? 'N/A' }}</td>
                                    <td class="is-num">UGX {{ number_format($invoice->balance_due) }}</td>
                                    <td style="{{ $invoice->isOverdue() ? 'color:var(--gh-error);' : '' }}">
                                        {{ $invoice->due_date->format('d M Y') }}
                                        @if($invoice->isOverdue())
                                            <span style="font-size:11px;">({{ $invoice->days_overdue }}d overdue)</span>
                                        @endif
                                    </td>
                                    <td><span class="gh-badge gh-badge--{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="gh-page" style="text-align:center; color:var(--gh-success); font-size:12.5px;">All invoices are paid!</div>
            @endif
        </div>
    @endcan
</div>
