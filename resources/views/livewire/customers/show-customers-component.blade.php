<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('customers.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="gh-sidebar__mark" style="width:48px; height:48px; border-radius:50%; font-size:16px;">{{ strtoupper(substr($customer->name, 0, 2)) }}</div>
                <div>
                    <div style="font-size:20px; font-weight:700;">{{ $customer->name }}</div>
                    <p class="gh-muted" style="font-size:12.5px;">{{ $customer->phone }}</p>
                </div>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('work-orders.create', ['customer' => $customer->id]) }}" class="gh-btn gh-btn--primary gh-btn--sm">New work order</a>
            @can('edit_customers')
                <a href="{{ route('customers.edit', $customer) }}" class="gh-btn gh-btn--sm">Edit</a>
            @endcan
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <!-- Vehicles -->
            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div class="gh-card__title">Vehicles ({{ $customer->vehicles->count() }})</div>
                    <a href="{{ route('vehicles.create', ['customer' => $customer->id]) }}" class="gh-btn gh-btn--sm">+ Add</a>
                </div>
                @if($customer->vehicles->count() > 0)
                    <div class="gh-grid-2">
                        @foreach($customer->vehicles as $vehicle)
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="gh-card" style="padding:12px; display:block;">
                                <p style="font-weight:700; font-size:13px;">{{ $vehicle->registration_number }}</p>
                                <p class="gh-muted" style="font-size:11.5px;">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="gh-muted" style="text-align:center; padding:16px 0;">No vehicles registered</p>
                @endif
            </div>

            <!-- Recent Work Orders -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Recent Work Orders</div>
                @if($customer->workOrders->count() > 0)
                    <div class="gh-stack" style="gap:4px;">
                        @foreach($customer->workOrders as $order)
                            <a href="{{ route('work-orders.show', $order) }}" style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-radius:var(--gh-radius);">
                                <span>
                                    <span class="is-ref">{{ $order->order_number }}</span>
                                    <span class="gh-muted" style="margin-left:8px; font-size:12px;">{{ $order->vehicle->registration_number }}</span>
                                </span>
                                <span class="gh-badge {{ $order->status_color !== 'ghost' ? 'gh-badge--'.($order->status_color === 'accent' ? 'primary' : ($order->status_color === 'secondary' ? 'info' : $order->status_color)) : '' }}">{{ ucfirst($order->status) }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="gh-muted" style="text-align:center; padding:16px 0;">No work orders yet</p>
                @endif
            </div>

            <!-- Recent Invoices -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Recent Invoices</div>
                @if($customer->invoices->count() > 0)
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Invoice</th><th>Date</th><th style="text-align:right;">Amount</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($customer->invoices as $invoice)
                                    <tr data-href="{{ route('invoices.show', $invoice) }}">
                                        <td class="is-ref"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                        <td class="gh-muted">{{ $invoice->created_at->format('d M Y') }}</td>
                                        <td class="is-num">UGX {{ number_format($invoice->total) }}</td>
                                        <td><span class="gh-badge {{ $invoice->status_color !== 'ghost' ? 'gh-badge--'.($invoice->status_color === 'accent' ? 'primary' : $invoice->status_color) : '' }}">{{ ucfirst($invoice->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="gh-muted" style="text-align:center; padding:16px 0;">No invoices yet</p>
                @endif
            </div>
        </div>

        <div class="gh-stack">
            <!-- Contact Info -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Contact Info</div>
                <div class="gh-stack" style="gap:10px; font-size:12.5px;">
                    <div>{{ $customer->phone }}</div>
                    @if($customer->email)
                        <div>{{ $customer->email }}</div>
                    @endif
                    @if($customer->address)
                        <div>{{ $customer->address }}</div>
                    @endif
                </div>
            </div>

            <!-- Stats -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Summary</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Total spent</span><b>UGX {{ number_format($customer->invoices->sum('total')) }}</b></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Work orders</span><span>{{ $customer->workOrders->count() }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Wash orders</span><span>{{ $customer->washOrders->count() }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Loyalty points</span><span class="gh-badge gh-badge--primary">{{ number_format($customer->loyalty_points) }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Customer since</span><span>{{ $customer->created_at->format('M Y') }}</span></div>
                </div>
            </div>

            @if($customer->notes)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:8px;">Notes</div>
                    <p style="font-size:12.5px;">{{ $customer->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
