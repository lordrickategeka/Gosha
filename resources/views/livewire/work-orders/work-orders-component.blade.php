<div class="gh-page">
    <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:20px; flex-wrap:wrap;">
        <div>
            <div class="gh-eyebrow">Workshop operations</div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:4px;">Work Orders</div>
            <p class="gh-muted" style="font-size:12.5px; max-width:560px; margin:6px 0 0;">Track active service jobs, filter operational load, and move vehicles cleanly through the workshop pipeline.</p>
        </div>

        @can('create_work_orders')
            <a href="{{ route('work-orders.create') }}" class="gh-btn gh-btn--primary">+ Check in vehicle</a>
        @endcan
    </div>

    <div class="gh-table-toolbar">
        <div class="gh-table-toolbar__filters">
            <button wire:click="$set('status', '')" class="gh-chip {{ $status === '' ? 'is-active' : '' }}">All open</button>
            <button wire:click="$set('status', 'in_progress')" class="gh-chip {{ $status === 'in_progress' ? 'is-active' : '' }}">In progress</button>
            <button wire:click="$set('status', 'quoted')" class="gh-chip {{ $status === 'quoted' ? 'is-active' : '' }}">Awaiting OK</button>
            <button wire:click="$set('status', 'quality_check')" class="gh-chip {{ $status === 'quality_check' ? 'is-active' : '' }}">QC</button>
            <button wire:click="$set('status', 'ready')" class="gh-chip {{ $status === 'ready' ? 'is-active' : '' }}">Ready</button>
        </div>

        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <label class="gh-search" style="width:190px;">
                ⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Plate, customer, WO no.">
            </label>
            <select wire:model.live="type" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All types</option>
                <option value="service">Service</option>
                <option value="repair">Repair</option>
                <option value="diagnostics">Diagnostics</option>
                <option value="bodywork">Bodywork</option>
                <option value="electrical">Electrical</option>
                <option value="ac">A/C</option>
                <option value="tyres">Tyres</option>
            </select>
            <select wire:model.live="technician" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All techs</option>
                @foreach($this->technicians as $tech)
                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                @endforeach
            </select>
            @if($search || $status || $type || $technician)
                <button wire:click="clearFilters" class="gh-btn gh-btn--sm">Clear</button>
            @endif
            <span class="gh-hint">Show</span>
            <select wire:model.live="perPage" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="6">6</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th class="is-index">#</th>
                        <th>WO</th>
                        <th>Vehicle</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Technician</th>
                        <th>Bay</th>
                        <th>Status</th>
                        <th style="text-align:right;">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workOrders as $iteration => $order)
                        @php
                            $badgeTone = match ($order->status_color) {
                                'accent' => 'primary',
                                'secondary' => 'info',
                                'ghost' => '',
                                default => $order->status_color,
                            };
                        @endphp
                        <tr data-href="{{ route('work-orders.show', $order) }}">
                            <td class="is-index">{{ $workOrders->firstItem() + $iteration }}</td>
                            <td class="is-ref">
                                <a href="{{ route('work-orders.show', $order) }}">{{ $order->order_number }}</a>
                                @if($order->is_combo)
                                    <span class="gh-badge gh-badge--primary" style="margin-left:4px;">COMBO</span>
                                @endif
                            </td>
                            <td>
                                <div class="gh-cell-stack">
                                    <b>{{ $order->vehicle?->registration_number ?? 'N/A' }}</b>
                                    <span>{{ trim(($order->vehicle?->make ?? '').' '.($order->vehicle?->model ?? '')) ?: '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="gh-cell-stack">
                                    <b>{{ $order->customer?->name ?? 'Walk-in' }}</b>
                                    <span>{{ $order->customer?->phone ?? '—' }}</span>
                                </div>
                            </td>
                            <td><span class="gh-badge">{{ ucfirst($order->type) }}</span></td>
                            <td>{{ $order->assignedTechnician?->name ?? '—' }}</td>
                            <td>{{ $order->serviceBay?->name ?? '—' }}</td>
                            <td>
                                <span class="gh-badge {{ $badgeTone ? 'gh-badge--'.$badgeTone : '' }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span>
                                @if($order->priority === 'urgent')
                                    <span class="gh-badge gh-badge--error" style="margin-left:4px;">URGENT</span>
                                @endif
                            </td>
                            <td class="is-num">UGX {{ number_format($order->subtotal) }}</td>
                            <td onclick="event.stopPropagation()">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-48 gh-card p-2 shadow-xl">
                                        <li><a href="{{ route('work-orders.show', $order) }}">View details</a></li>
                                        @can('edit_work_orders')
                                            <li><a href="{{ route('work-orders.edit', $order) }}">Edit</a></li>
                                        @endcan
                                        @can('change_work_order_status')
                                            @if($order->canStart())
                                                <li><button wire:click="startWorkOrder({{ $order->id }})">Start work</button></li>
                                            @endif
                                            @if($order->canComplete())
                                                <li><button wire:click="markReady({{ $order->id }})">Mark ready</button></li>
                                            @endif
                                            @if($order->canDeliver())
                                                <li><button wire:click="deliver({{ $order->id }})">Deliver</button></li>
                                            @endif
                                        @endcan
                                        @can('view_invoices')
                                            @if($order->invoice)
                                                <li><a href="{{ route('invoices.show', $order->invoice) }}">View invoice</a></li>
                                            @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No work orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workOrders->hasPages())
            <div class="gh-pagination">
                <span class="gh-hint">Showing {{ $workOrders->firstItem() }}–{{ $workOrders->lastItem() }} of {{ $workOrders->total() }} work orders</span>
                <div>{{ $workOrders->links() }}</div>
            </div>
        @else
            <div class="gh-pagination">
                <span class="gh-hint">{{ $workOrders->total() }} work order{{ $workOrders->total() === 1 ? '' : 's' }}</span>
            </div>
        @endif
    </div>
</div>
