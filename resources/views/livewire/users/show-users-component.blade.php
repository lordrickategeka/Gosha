<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-neutral text-neutral-content rounded-full w-14">
                        <span class="text-xl">{{ substr($user->name, 0, 2) }}</span>
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                    <div class="flex items-center gap-2">
                        @foreach($user->roles as $role)
                            <span class="badge badge-primary badge-sm">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                        @endforeach
                        <span class="badge badge-{{ $user->is_active ? 'success' : 'error' }} badge-sm">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @can('edit users')
            <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost">Edit</a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
                    <div class="stat-title text-xs">Work Orders</div>
                    <div class="stat-value text-lg">{{ $this->stats['work_orders'] }}</div>
                </div>
                <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
                    <div class="stat-title text-xs">Wash Orders</div>
                    <div class="stat-value text-lg">{{ $this->stats['wash_orders'] }}</div>
                </div>
                <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
                    <div class="stat-title text-xs">Completed Today</div>
                    <div class="stat-value text-lg text-success">{{ $this->stats['completed_today'] }}</div>
                </div>
                <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
                    <div class="stat-title text-xs">Total Commission</div>
                    <div class="stat-value text-lg">UGX {{ number_format($this->stats['commissions_total']) }}</div>
                </div>
            </div>

            <!-- Recent Commissions -->
            @if($user->commissions->count() > 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Recent Commissions</h2>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Date</th><th>Order</th><th>Type</th><th class="text-right">Amount</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($user->commissions as $commission)
                                        <tr>
                                            <td>{{ $commission->created_at->format('d M') }}</td>
                                            <td class="font-mono text-sm">{{ $commission->work_order_id ? 'WO' : 'WASH' }}-{{ $commission->work_order_id ?? $commission->wash_order_id }}</td>
                                            <td>{{ ucfirst($commission->type) }}</td>
                                            <td class="text-right font-medium text-success">UGX {{ number_format($commission->amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <!-- Contact -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Contact Info</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <span>{{ $user->email }}</span>
                        </div>
                        @if($user->phone)
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                <span>{{ $user->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Branches -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Assigned Branches</h2>
                    @if($user->branches->count() > 0)
                        <div class="space-y-2">
                            @foreach($user->branches as $branch)
                                <div class="p-2 bg-base-200 rounded-lg">
                                    <p class="font-medium">{{ $branch->name }}</p>
                                    <p class="text-xs text-base-content/60">{{ $branch->address }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-base-content/50">Access to all branches</p>
                    @endif
                </div>
            </div>

            <!-- Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Details</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-base-content/60">Created</span><span>{{ $user->created_at->format('d M Y') }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Last Login</span><span>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
