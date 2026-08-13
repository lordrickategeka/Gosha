<div class="gh-page">
    <div style="display:flex; align-items:flex-end; justify-content:space-between; gap:20px; flex-wrap:wrap;">
        <div>
            <div class="gh-eyebrow">Wash operations</div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:4px;">Wash Bay</div>
            <p class="gh-muted" style="font-size:12.5px; max-width:560px; margin:6px 0 0;">Manage live wash queue activity, assign bays, and complete orders quickly.</p>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <div class="gh-segmented">
                <button wire:click="$set('view', 'queue')" class="{{ $view === 'queue' ? 'is-active' : '' }}">Queue view</button>
                <button wire:click="$set('view', 'list')" class="{{ $view === 'list' ? 'is-active' : '' }}">List view</button>
            </div>
            @can('create_wash_orders')
                <a href="{{ route('wash-orders.create') }}" class="gh-btn gh-btn--primary">+ New wash</a>
            @endcan
        </div>
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Queued today</span>
            <span class="gh-stat__value">{{ $this->statsTodayQueued }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">In progress</span>
            <span class="gh-stat__value">{{ $this->statsInProgress }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Completed today</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">{{ $this->statsCompletedToday }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Bays available</span>
            <span class="gh-stat__value">{{ $this->statsAvailableBays }}</span>
        </div>
    </div>

    @if($view === 'queue')
        <span class="gh-hint">Wash bays</span>
        <div class="gh-grid-3">
            @foreach($this->washBays as $bay)
                @php
                    $tone = $bay->status instanceof \App\Domains\Operations\Enums\WashBayStatus ? $bay->status->value : $bay->status;
                @endphp
                <div class="gh-bay {{ $tone === 'occupied' ? 'is-occupied' : ($tone === 'maintenance' ? 'is-maintenance' : '') }}">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span class="gh-bay__name">{{ $bay->name }}</span>
                        <span class="gh-badge {{ $tone === 'available' ? 'gh-badge--success' : ($tone === 'occupied' ? 'gh-badge--warning' : 'gh-badge--error') }}">
                            {{ $bay->status instanceof \App\Domains\Operations\Enums\WashBayStatus ? $bay->status->label() : ucfirst($bay->status) }}
                        </span>
                    </div>
                    @if($bay->currentWashOrder)
                        <span class="gh-muted" style="font-size:11px;">{{ $bay->currentWashOrder->vehicle->registration_number }} · {{ ucfirst($bay->currentWashOrder->wash_type) }}</span>
                        <button wire:click="completeWash({{ $bay->currentWashOrder->id }})" class="gh-btn gh-btn--sm gh-btn--block" style="margin-top:6px;">Complete</button>
                    @else
                        <span class="gh-bay__state">Ready for next vehicle</span>
                    @endif
                </div>
            @endforeach
        </div>

        <span class="gh-hint">Click Assign &amp; start to move a vehicle into a bay. Click Complete to release it.</span>
        <div class="gh-board" style="grid-template-columns: repeat(3, 1fr);">
            <div class="gh-board__col gh-board__col--waiting">
                <div class="gh-board__head"><span style="display:flex; align-items:center; gap:8px;"><span class="gh-board__dot"></span>Waiting</span><span class="gh-board__count">{{ $this->queue->count() }}</span></div>
                @forelse($this->queue as $order)
                    <div class="gh-board__card gh-board__card--waiting">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <b>{{ $order->vehicle->registration_number }}</b>
                            @if($order->priority === 'priority')
                                <span class="gh-badge gh-badge--warning">PRIORITY</span>
                            @endif
                        </div>
                        <span style="font-size:12px;">{{ $order->customer->name }}</span>
                        <div class="gh-board__meta"><span>{{ ucfirst($order->wash_type) }} · {{ $order->source_badge }}</span><span>Queue pos. {{ $order->queue_position }}</span></div>
                        <div style="display:flex; gap:6px; margin-top:4px;">
                            @if($this->availableBays->isNotEmpty())
                                <button wire:click="openAssignBayModal({{ $order->id }})" class="gh-btn gh-btn--sm gh-btn--primary" style="flex:1;">Assign &amp; start</button>
                            @else
                                <button class="gh-btn gh-btn--sm" style="flex:1;" disabled>No bay available</button>
                            @endif
                            @if($order->priority !== 'priority')
                                <button wire:click="prioritize({{ $order->id }})" class="gh-btn gh-btn--sm" title="Prioritize">↑</button>
                            @endif
                            <button wire:click="cancel({{ $order->id }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);" title="Cancel">✕</button>
                        </div>
                    </div>
                @empty
                    <span class="gh-muted" style="font-size:12px;">Queue is empty</span>
                @endforelse
            </div>

            <div class="gh-board__col gh-board__col--active">
                <div class="gh-board__head"><span style="display:flex; align-items:center; gap:8px;"><span class="gh-board__dot"></span>Washing</span><span class="gh-board__count">{{ $this->inProgress->count() }}</span></div>
                @forelse($this->inProgress as $order)
                    <div class="gh-board__card gh-board__card--active">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <b>{{ $order->vehicle->registration_number }}</b>
                        </div>
                        <span style="font-size:12px;">{{ ucfirst($order->wash_type) }} · {{ $order->washBay?->name ?? 'No bay' }}</span>
                        <div class="gh-board__meta">
                            <span>{{ $order->assignedAttendant?->name ?? 'Unassigned' }}</span>
                            <span>{{ $order->started_at?->diffForHumans() }}</span>
                        </div>
                        <button wire:click="completeWash({{ $order->id }})" class="gh-btn gh-btn--sm gh-btn--primary" style="margin-top:4px;">Complete</button>
                    </div>
                @empty
                    <span class="gh-muted" style="font-size:12px;">Nothing washing right now</span>
                @endforelse
            </div>

            <div class="gh-board__col gh-board__col--done">
                <div class="gh-board__head"><span style="display:flex; align-items:center; gap:8px;"><span class="gh-board__dot"></span>Completed today</span><span class="gh-board__count">{{ $this->completedTodayList->count() }}</span></div>
                @forelse($this->completedTodayList as $order)
                    <div class="gh-board__card gh-board__card--done">
                        <b>{{ $order->vehicle->registration_number }}</b>
                        <span style="font-size:12px;">{{ $order->customer->name }}</span>
                        <div class="gh-board__meta"><span>{{ $order->washBay?->name ?? '—' }}</span><span>{{ $order->completed_at?->format('H:i') }}</span></div>
                    </div>
                @empty
                    <span class="gh-muted" style="font-size:12px;">None yet today</span>
                @endforelse
            </div>
        </div>
    @else
        <div class="gh-table-toolbar">
            <div class="gh-table-toolbar__filters">
                <label class="gh-search" style="width:190px;">
                    ⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search…">
                </label>
                <select wire:model.live="status" class="gh-select" style="padding:6px 10px; font-size:12px;">
                    <option value="">All statuses</option>
                    <option value="queued">Queued</option>
                    <option value="in_progress">In progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="source" class="gh-select" style="padding:6px 10px; font-size:12px;">
                    <option value="">All sources</option>
                    <option value="walk_in">Walk-in</option>
                    <option value="combo">Combo</option>
                    <option value="appointment">Appointment</option>
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
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
                            <th class="is-index">#</th><th>Order</th><th>Vehicle</th><th>Customer</th>
                            <th>Type</th><th>Source</th><th>Bay</th><th>Status</th><th>Created</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($washOrders as $index => $order)
                            <tr data-href="{{ route('wash-orders.show', $order) }}">
                                <td class="is-index">{{ $washOrders->firstItem() + $index }}</td>
                                <td class="is-ref"><a href="{{ route('wash-orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td><b>{{ $order->vehicle->registration_number }}</b></td>
                                <td>{{ $order->customer->name }}</td>
                                <td><span class="gh-badge">{{ ucfirst($order->wash_type) }}</span></td>
                                <td><span class="gh-badge {{ $order->source === 'combo' ? 'gh-badge--primary' : '' }}">{{ $order->source_badge }}</span></td>
                                <td>{{ $order->washBay?->name ?? '—' }}</td>
                                <td>
                                    <span class="gh-badge {{ $order->status_color !== 'ghost' ? 'gh-badge--'.$order->status_color : '' }}">
                                        {{ $order->status instanceof \App\Domains\Operations\Enums\WashOrderStatus ? $order->status->label() : ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="gh-muted">{{ $order->created_at->format('d M H:i') }}</td>
                                <td onclick="event.stopPropagation()">
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                        <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-40 gh-card p-2 shadow-xl">
                                            <li><a href="{{ route('wash-orders.show', $order) }}">View</a></li>
                                            @if($order->canStart())
                                                <li><button wire:click="startWash({{ $order->id }})">Start</button></li>
                                            @endif
                                            @if($order->canComplete())
                                                <li><button wire:click="completeWash({{ $order->id }})">Complete</button></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No wash orders found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($washOrders->hasPages())
                <div class="gh-pagination">
                    <span class="gh-hint">Showing {{ $washOrders->firstItem() }}–{{ $washOrders->lastItem() }} of {{ $washOrders->total() }}</span>
                    <div>{{ $washOrders->links() }}</div>
                </div>
            @endif
        </div>
    @endif

    <!-- Assign Bay Modal -->
    @if($showAssignBayModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:420px;">
                <div class="gh-card__title" style="margin-bottom:16px;">Assign bay &amp; start wash</div>

                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Select wash bay *</span>
                    <select wire:model="selectedBayId" class="gh-select">
                        <option value="">— Choose a bay —</option>
                        @foreach($this->availableBays as $bay)
                            <option value="{{ $bay->id }}">{{ $bay->name }} ({{ ucfirst($bay->bay_type instanceof \App\Domains\Operations\Enums\WashBayType ? $bay->bay_type->label() : $bay->bay_type) }})</option>
                        @endforeach
                    </select>
                    @error('selectedBayId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>

                <div class="gh-field" style="margin-bottom:18px;">
                    <span class="gh-label">Assign attendant (optional)</span>
                    <select wire:model="selectedAttendantId" class="gh-select">
                        <option value="">— Unassigned —</option>
                        @foreach($this->attendants as $attendant)
                            <option value="{{ $attendant->id }}">{{ $attendant->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px;">
                    <button wire:click="closeAssignBayModal" class="gh-btn">Cancel</button>
                    <button wire:click="confirmAssignAndStart" class="gh-btn gh-btn--primary">Start wash</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeAssignBayModal"></div>
        </div>
    @endif
</div>
