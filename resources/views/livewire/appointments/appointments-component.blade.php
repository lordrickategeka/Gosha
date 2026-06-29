<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Appointments</h1>
            <p class="text-base-content/60">{{ $todayCount }} today, {{ $upcomingCount }} upcoming</p>
        </div>
        @can('create_appointments')
        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Appointment
        </a>
        @endcan
    </div>

    <!-- Filters (in table header) -->
    <div class="card bg-base-100 shadow-sm mb-4">
        <div class="flex flex-wrap items-center gap-2 p-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="input input-bordered input-sm w-40" />
            <input type="date" wire:model.live="date" class="input input-bordered input-sm w-36" />
            <select wire:model.live="status" class="select select-bordered select-sm w-36">
                <option value="">All Status</option>
                <option value="scheduled">Scheduled</option>
                <option value="confirmed">Confirmed</option>
                <option value="checked_in">Checked In</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="no_show">No Show</option>
            </select>
            <div class="flex gap-1">
                <button wire:click="$set('date', '{{ now()->format('Y-m-d') }}')" class="btn btn-xs btn-ghost">Today</button>
                <button wire:click="$set('date', '{{ now()->addDay()->format('Y-m-d') }}')" class="btn btn-xs btn-ghost">Tomorrow</button>
            </div>
            @if($search || $date || $status)
            <button wire:click="clearFilters" class="btn btn-xs btn-ghost" title="Clear filters">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service Type</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                        <tr class="hover">
                            <td>
                                <div class="font-medium">{{ $apt->scheduled_date->format('D, d M') }}</div>
                                <div class="text-sm text-base-content/60">{{ $apt->scheduled_time->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $apt->customer->name }}</div>
                                <div class="text-sm text-base-content/60">{{ $apt->customer->phone }}</div>
                            </td>
                            <td>
                                <div>{{ $apt->vehicle->registration_number }}</div>
                                <div class="text-xs text-base-content/60">{{ $apt->vehicle->make }} {{ $apt->vehicle->model }}</div>
                            </td>
                            <td><span class="badge badge-ghost badge-sm">{{ ucfirst($apt->type) }}</span></td>
                            <td><span class="badge badge-{{ $apt->status_color }} badge-sm">{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</span></td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44">
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
                        <tr><td colspan="6" class="text-center py-8 text-base-content/50">No appointments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appointments->hasPages())
            <div class="p-4 border-t border-base-200">{{ $appointments->links() }}</div>
        @endif
    </div>
</div>
