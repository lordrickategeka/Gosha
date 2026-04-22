<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Vendors</h1>
            <p class="text-base-content/60">Manage all registered vendors on the platform</p>
        </div>
        <a href="{{ route('platform.vendors.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Vendor
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-box shadow-sm p-4">
            <div class="stat-title text-xs">Total Vendors</div>
            <div class="stat-value text-2xl">{{ $stats['total'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm p-4">
            <div class="stat-title text-xs">Active</div>
            <div class="stat-value text-2xl text-success">{{ $stats['active'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm p-4">
            <div class="stat-title text-xs">On Trial</div>
            <div class="stat-value text-2xl text-warning">{{ $stats['trial'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm p-4">
            <div class="stat-title text-xs">Suspended</div>
            <div class="stat-value text-2xl text-error">{{ $stats['suspended'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or email..." class="input input-bordered w-full sm:max-w-xs" />
                <select wire:model.live="statusFilter" class="select select-bordered w-full sm:w-auto">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="trial">Trial</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Vendors Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Contact</th>
                        <th>Branches</th>
                        <th>Users</th>
                        <th>Billing</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-10">
                                            <span>{{ strtoupper(substr($vendor->name, 0, 2)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('platform.vendors.show', $vendor) }}" class="font-bold link link-hover">{{ $vendor->name }}</a>
                                        <p class="text-xs text-base-content/60">{{ $vendor->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p>{{ $vendor->email }}</p>
                                @if($vendor->phone)
                                    <p class="text-xs text-base-content/60">{{ $vendor->phone }}</p>
                                @endif
                            </td>
                            <td>{{ $vendor->branches_count }}</td>
                            <td>{{ $vendor->users_count }}</td>
                            <td>
                                @if($vendor->billingConfig)
                                    <span class="badge badge-ghost badge-sm">{{ ucfirst(str_replace('_', ' ', $vendor->billingConfig->billing_model)) }}</span>
                                @else
                                    <span class="text-base-content/40 text-sm">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($vendor->status === 'active')
                                    <span class="badge badge-success badge-sm">Active</span>
                                @elseif($vendor->status === 'trial')
                                    <span class="badge badge-warning badge-sm">Trial</span>
                                    @if($vendor->trial_ends_at)
                                        <p class="text-xs text-base-content/50 mt-1">
                                            {{ $vendor->isTrialExpired() ? 'Expired' : 'Ends ' . $vendor->trial_ends_at->diffForHumans() }}
                                        </p>
                                    @endif
                                @elseif($vendor->status === 'suspended')
                                    <span class="badge badge-error badge-sm">Suspended</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44">
                                        <li><a href="{{ route('platform.vendors.show', $vendor) }}">View Details</a></li>
                                        <li>
                                            <button wire:click="toggleStatus({{ $vendor->id }})" wire:confirm="Are you sure you want to {{ $vendor->status === 'suspended' ? 'activate' : 'suspend' }} this vendor?">
                                                {{ $vendor->status === 'suspended' ? 'Activate' : 'Suspend' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-base-content/50">
                                No vendors found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
            <div class="p-4 border-t border-base-200">{{ $vendors->links() }}</div>
        @endif
    </div>
</div>
