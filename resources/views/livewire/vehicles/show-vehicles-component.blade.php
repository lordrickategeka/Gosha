<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('vehicles.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:20px; font-weight:700;">{{ $vehicle->registration_number }}</span>
                    <span class="gh-plate">{{ $vehicle->registration_number }}</span>
                </div>
                <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</p>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('work-orders.create', ['customer' => $vehicle->customer_id, 'vehicle' => $vehicle->id]) }}" class="gh-btn gh-btn--primary gh-btn--sm">New work order</a>
            @can('edit_vehicles')
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="gh-btn gh-btn--sm">Edit</a>
            @endcan
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <!-- Work Order History -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Work Order History</div>
                @if($vehicle->workOrders->count() > 0)
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Order</th><th>Type</th><th>Date</th><th>Status</th><th style="text-align:right;">Total</th></tr></thead>
                            <tbody>
                                @foreach($vehicle->workOrders as $order)
                                    <tr data-href="{{ route('work-orders.show', $order) }}">
                                        <td class="is-ref"><a href="{{ route('work-orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                        <td><span class="gh-badge">{{ ucfirst($order->type) }}</span></td>
                                        <td class="gh-muted">{{ $order->created_at->format('d M Y') }}</td>
                                        <td><span class="gh-badge {{ $order->status_color !== 'ghost' ? 'gh-badge--'.($order->status_color === 'accent' ? 'primary' : ($order->status_color === 'secondary' ? 'info' : $order->status_color)) : '' }}">{{ ucfirst($order->status) }}</span></td>
                                        <td class="is-num">UGX {{ number_format($order->subtotal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="gh-muted" style="text-align:center; padding:16px 0;">No work orders yet</p>
                @endif
            </div>

            <!-- Wash History -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Wash History</div>
                @if($vehicle->washOrders->count() > 0)
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Order</th><th>Type</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($vehicle->washOrders as $order)
                                    <tr data-href="{{ route('wash-orders.show', $order) }}">
                                        <td class="is-ref"><a href="{{ route('wash-orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                        <td>{{ ucfirst($order->wash_type) }}</td>
                                        <td class="gh-muted">{{ $order->created_at->format('d M Y') }}</td>
                                        <td><span class="gh-badge {{ $order->status_color !== 'ghost' ? 'gh-badge--'.$order->status_color : '' }}">{{ ucfirst($order->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="gh-muted" style="text-align:center; padding:16px 0;">No wash orders yet</p>
                @endif
            </div>
        </div>

        <div class="gh-stack">
            <!-- Vehicle Details -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Vehicle Details</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Make</span><span>{{ $vehicle->make ?? '—' }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Model</span><span>{{ $vehicle->model ?? '—' }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Year</span><span>{{ $vehicle->year ?? '—' }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Color</span><span>{{ $vehicle->color ?? '—' }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Fuel type</span><span>{{ ucfirst($vehicle->fuel_type ?? '—') }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Transmission</span><span>{{ ucfirst($vehicle->transmission ?? '—') }}</span></div>
                    @if($vehicle->vin)
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">VIN</span><span style="font-size:11px;">{{ $vehicle->vin }}</span></div>
                    @endif
                </div>
            </div>

            <!-- Owner -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Owner</div>
                <a href="{{ route('customers.show', $vehicle->customer) }}" class="gh-card" style="display:flex; align-items:center; gap:10px; padding:11px; background:var(--gh-base-200);">
                    <div class="gh-sidebar__mark" style="width:38px; height:38px; border-radius:50%; font-size:13px;">{{ strtoupper(substr($vehicle->customer->name, 0, 2)) }}</div>
                    <div>
                        <p style="font-weight:600; font-size:13px;">{{ $vehicle->customer->name }}</p>
                        <p class="gh-muted" style="font-size:12px;">{{ $vehicle->customer->phone }}</p>
                    </div>
                </a>
            </div>

            <!-- Stats -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Summary</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Work orders</span><b>{{ $vehicle->workOrders->count() }}</b></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Washes</span><b>{{ $vehicle->washOrders->count() }}</b></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Last service</span><span>{{ $vehicle->workOrders->first()?->created_at->format('d M Y') ?? 'Never' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
