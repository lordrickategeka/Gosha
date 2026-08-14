<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('wash-orders.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">{{ $washOrder->order_number }}</span>
                    <span class="gh-badge {{ $washOrder->status_color !== 'ghost' ? 'gh-badge--'.$washOrder->status_color : '' }}">{{ $washOrder->status->label() }}</span>
                    @if($washOrder->source === 'combo')
                        <span class="gh-badge gh-badge--primary">COMBO</span>
                    @endif
                </div>
                <p class="gh-muted" style="font-size:12px; margin-top:2px;">{{ $washOrder->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div style="display:flex; gap:8px;">
            @can('change_wash_order_status')
                @if($washOrder->canStart())
                    @if($this->availableBays->count() > 0)
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="gh-btn gh-btn--primary">Start wash</label>
                            <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-48 gh-card p-2 shadow-xl">
                                @foreach($this->availableBays as $bay)
                                    <li><button wire:click="start({{ $bay->id }})">{{ $bay->name }}</button></li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <button class="gh-btn" disabled>No bay available</button>
                    @endif
                @endif
                @if($washOrder->canComplete())
                    <button wire:click="complete" class="gh-btn gh-btn--primary">Complete wash</button>
                @endif
                @if($washOrder->status === 'queued')
                    <button wire:click="cancel" class="gh-btn" style="color:var(--gh-error);">Cancel</button>
                @endif
            @endcan
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <!-- Vehicle & Customer -->
            <div class="gh-card gh-card--pad">
                <div class="gh-grid-2">
                    <div>
                        <p class="gh-eyebrow" style="margin-bottom:4px;">Vehicle</p>
                        <p style="font-weight:700; font-size:16px;">{{ $washOrder->vehicle->registration_number }}</p>
                        <p class="gh-muted" style="font-size:12px;">{{ $washOrder->vehicle->make }} {{ $washOrder->vehicle->model }}</p>
                    </div>
                    <div>
                        <p class="gh-eyebrow" style="margin-bottom:4px;">Customer</p>
                        <p style="font-weight:700; font-size:13px;">{{ $washOrder->customer->name }}</p>
                        <p class="gh-muted" style="font-size:12px;">{{ $washOrder->customer->phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Services -->
            <div class="gh-card gh-card--flush">
                <div class="gh-card__head"><span class="gh-card__title">Services</span></div>
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Service</th><th style="text-align:right;">Price</th></tr></thead>
                        <tbody>
                            @foreach($washOrder->items as $item)
                                <tr><td>{{ $item->description }}</td><td class="is-num">UGX {{ number_format($item->total) }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td style="text-align:right; font-weight:700; padding:10px 18px;">Total:</td><td class="is-num" style="font-size:14px;">UGX {{ number_format($washOrder->total) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($washOrder->customer_notes)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:8px;">Notes</div>
                    <p style="font-size:13px;">{{ $washOrder->customer_notes }}</p>
                </div>
            @endif

            @if($washOrder->workOrder)
                <div class="gh-card gh-card--pad" style="background:var(--gh-primary-tint); border-color:var(--gh-primary);">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div class="gh-card__title">Linked Work Order</div>
                            <p class="is-ref" style="margin-top:2px;">{{ $washOrder->workOrder->order_number }}</p>
                        </div>
                        <a href="{{ route('work-orders.show', $washOrder->workOrder) }}" class="gh-btn gh-btn--sm">View</a>
                    </div>
                </div>
            @endif
        </div>

        <div class="gh-stack">
            <!-- Status Timeline -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Timeline</div>
                <div class="gh-timeline">
                    @php
                        $washSteps = [
                            ['label' => 'Queued', 'time' => $washOrder->queued_at, 'done' => in_array($washOrder->status->value, ['queued', 'in_progress', 'completed'])],
                            ['label' => 'Started', 'time' => $washOrder->started_at, 'done' => in_array($washOrder->status->value, ['in_progress', 'completed'])],
                            ['label' => 'Completed', 'time' => $washOrder->completed_at, 'done' => $washOrder->status->value === 'completed'],
                        ];
                    @endphp
                    @foreach ($washSteps as $step)
                        <div class="gh-timeline__row">
                            <span class="gh-timeline__time">{{ $step['time']?->format('H:i') ?? '' }}</span>
                            <div class="gh-timeline__body">
                                <span class="gh-timeline__what" style="{{ $step['done'] ? '' : 'color:var(--gh-ink-faint);' }}">{{ $step['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Details -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Details</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Wash type</span><span class="gh-badge">{{ ucwords(str_replace('_', ' ', $washOrder->wash_type)) }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Source</span><span>{{ $washOrder->source_badge }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Bay</span><span>{{ $washOrder->washBay?->name ?? '—' }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Attendant</span><span>{{ $washOrder->assignedAttendant?->name ?? '—' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
