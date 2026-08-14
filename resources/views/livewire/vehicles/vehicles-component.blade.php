<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Vehicles</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">All registered vehicles</p>
        </div>
        @can('create_vehicles')
            <a href="{{ route('vehicles.create') }}" class="gh-btn gh-btn--primary">+ Add vehicle</a>
        @endcan
    </div>

    <label class="gh-search" style="width:320px;">
        ⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by reg number, make, model, or owner…">
    </label>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Registration</th><th>Make / Model</th><th>Year</th><th>Color</th><th>Owner</th><th></th></tr></thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr data-href="{{ route('vehicles.show', $vehicle) }}">
                            <td><a href="{{ route('vehicles.show', $vehicle) }}" class="gh-plate">{{ $vehicle->registration_number }}</a></td>
                            <td><b>{{ $vehicle->make }}</b> <span class="gh-muted">{{ $vehicle->model }}</span></td>
                            <td>{{ $vehicle->year ?? '—' }}</td>
                            <td>{{ $vehicle->color ?? '—' }}</td>
                            <td>
                                <div class="gh-cell-stack">
                                    <a href="{{ route('customers.show', $vehicle->customer) }}" class="is-ref">{{ $vehicle->customer->name }}</a>
                                    <span>{{ $vehicle->customer->phone }}</span>
                                </div>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-44 gh-card p-2 shadow-xl">
                                        <li><a href="{{ route('vehicles.show', $vehicle) }}">View history</a></li>
                                        @can('edit_vehicles')
                                            <li><a href="{{ route('vehicles.edit', $vehicle) }}">Edit</a></li>
                                        @endcan
                                        <li><a href="{{ route('work-orders.create', ['vehicle' => $vehicle->id]) }}">New work order</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No vehicles found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
            <div class="gh-pagination">{{ $vehicles->links() }}</div>
        @endif
    </div>
</div>
