<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Appointments</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">{{ $todayCount }} today, {{ $upcomingCount }} upcoming</p>
        </div>
        @can('create_appointments')
            <a href="{{ route('appointments.create') }}" class="gh-btn gh-btn--primary">+ New appointment</a>
        @endcan
    </div>

    <div class="gh-card gh-card--pad">
        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="gh-input" style="width:10rem;">
            <input type="date" wire:model.live="date" class="gh-input" style="width:9.5rem;">
            <select wire:model.live="status" class="gh-select" style="width:9.5rem;">
                <option value="">All Status</option>
                <option value="scheduled">Scheduled</option>
                <option value="confirmed">Confirmed</option>
                <option value="checked_in">Checked In</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="no_show">No Show</option>
            </select>
            <div style="display:flex; gap:6px;">
                <button wire:click="$set('date', '{{ now()->format('Y-m-d') }}')" class="gh-btn gh-btn--sm">Today</button>
                <button wire:click="$set('date', '{{ now()->addDay()->format('Y-m-d') }}')" class="gh-btn gh-btn--sm">Tomorrow</button>
            </div>
            @if($search || $date || $status)
                <button wire:click="clearFilters" class="gh-btn gh-btn--sm" title="Clear filters">✕</button>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service Type</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $apt->scheduled_date->format('D, d M') }}</div>
                                <div class="gh-muted" style="font-size:11px;">{{ $apt->scheduled_time->format('H:i') }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600;">{{ $apt->customer->name }}</div>
                                <div class="gh-muted" style="font-size:11px;">{{ $apt->customer->phone }}</div>
                            </td>
                            <td>
                                <div>{{ $apt->vehicle->registration_number }}</div>
                                <div class="gh-muted" style="font-size:10.5px;">{{ $apt->vehicle->make }} {{ $apt->vehicle->model }}</div>
                            </td>
                            <td><span class="gh-badge">{{ ucfirst($apt->type) }}</span></td>
                            <td><span class="gh-badge gh-badge--{{ $apt->status_color }}">{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</span></td>
                            <td style="text-align:right;">
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44 border border-base-300">
                                        @if($apt->status === 'scheduled')
                                            <li><button wire:click="confirm({{ $apt->id }})">Confirm</button></li>
                                        @endif
                                        @if(in_array($apt->status, ['scheduled', 'confirmed']))
                                            <li><button wire:click="checkIn({{ $apt->id }})">Check In</button></li>
                                            <li><button wire:click="cancel({{ $apt->id }})">Cancel</button></li>
                                            <li><button wire:click="noShow({{ $apt->id }})">No Show</button></li>
                                        @endif
                                        @if($apt->status === 'checked_in')
                                            <li><a href="{{ route('work-orders.create', ['customer' => $apt->customer_id, 'vehicle' => $apt->vehicle_id]) }}">Create Work Order</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appointments->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $appointments->links() }}</div>
        @endif
    </div>
</div>
