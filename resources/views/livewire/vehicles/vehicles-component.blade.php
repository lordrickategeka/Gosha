<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Vehicles</h1>
            <p class="text-base-content/60">All registered vehicles</p>
        </div>
        @can('create vehicles')
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Vehicle
        </a>
        @endcan
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by reg number, make, model, or owner..." class="input input-bordered w-full max-w-md" />
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Registration</th>
                        <th>Make / Model</th>
                        <th>Year</th>
                        <th>Color</th>
                        <th>Owner</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr class="hover">
                            <td>
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="font-bold font-mono link link-hover">{{ $vehicle->registration_number }}</a>
                            </td>
                            <td>
                                <span class="font-medium">{{ $vehicle->make }}</span>
                                <span class="text-base-content/60">{{ $vehicle->model }}</span>
                            </td>
                            <td>{{ $vehicle->year ?? '-' }}</td>
                            <td>{{ $vehicle->color ?? '-' }}</td>
                            <td>
                                <a href="{{ route('customers.show', $vehicle->customer) }}" class="link link-hover">{{ $vehicle->customer->name }}</a>
                                <p class="text-xs text-base-content/60">{{ $vehicle->customer->phone }}</p>
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44">
                                        <li><a href="{{ route('vehicles.show', $vehicle) }}">View History</a></li>
                                        @can('edit vehicles')
                                            <li><a href="{{ route('vehicles.edit', $vehicle) }}">Edit</a></li>
                                        @endcan
                                        <li><a href="{{ route('work-orders.create', ['vehicle' => $vehicle->id]) }}">New Work Order</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-base-content/50">No vehicles found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
            <div class="p-4 border-t border-base-200">{{ $vehicles->links() }}</div>
        @endif
    </div>
</div>
