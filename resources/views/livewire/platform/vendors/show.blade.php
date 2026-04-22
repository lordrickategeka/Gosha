<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('platform.vendors.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">{{ $vendor->name }}</h1>
                <p class="text-base-content/60">Vendor Details</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if($vendor->status === 'trial')
                <button wire:click="activateFromTrial" wire:confirm="Activate this vendor and end their trial?" class="btn btn-success btn-sm">Activate</button>
            @endif
            <button wire:click="toggleStatus" wire:confirm="Are you sure?" class="btn btn-sm {{ $vendor->status === 'suspended' ? 'btn-success' : 'btn-error' }} btn-outline">
                {{ $vendor->status === 'suspended' ? 'Reactivate' : 'Suspend' }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Vendor Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Business Info Card -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Business Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                        <div>
                            <p class="text-xs text-base-content/60">Email</p>
                            <p class="font-medium">{{ $vendor->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60">Phone</p>
                            <p class="font-medium">{{ $vendor->phone ?: '—' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs text-base-content/60">Address</p>
                            <p class="font-medium">{{ $vendor->address ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60">Slug</p>
                            <p class="font-mono text-sm">{{ $vendor->slug }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60">Created</p>
                            <p class="text-sm">{{ $vendor->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branches -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Branches ({{ $vendor->branches->count() }})</h2>
                    <div class="overflow-x-auto mt-2">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Users</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendor->branches as $branch)
                                    <tr>
                                        <td>
                                            <span class="font-medium">{{ $branch->name }}</span>
                                            @if($branch->is_main) <span class="badge badge-primary badge-xs ml-1">Main</span> @endif
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ $branch->email ?: $branch->phone ?: '—' }}</span>
                                        </td>
                                        <td>{{ $branch->users_count }}</td>
                                        <td>
                                            <span class="badge badge-sm {{ $branch->is_active ? 'badge-success' : 'badge-error' }}">
                                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-base-content/50">No branches</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Users -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Users ({{ $vendor->users->count() }})</h2>
                    <div class="overflow-x-auto mt-2">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendor->users as $user)
                                    <tr>
                                        <td class="font-medium">{{ $user->name }}</td>
                                        <td class="text-sm">{{ $user->email }}</td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-ghost badge-sm">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge badge-sm {{ $user->is_active ? 'badge-success' : 'badge-error' }}">
                                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-base-content/50">No users</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Status & Billing -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Status</h2>
                    <div class="mt-2 space-y-3">
                        <div>
                            @if($vendor->status === 'active')
                                <span class="badge badge-success badge-lg">Active</span>
                            @elseif($vendor->status === 'trial')
                                <span class="badge badge-warning badge-lg">Trial</span>
                            @elseif($vendor->status === 'suspended')
                                <span class="badge badge-error badge-lg">Suspended</span>
                            @endif
                        </div>

                        @if($vendor->status === 'trial' && $vendor->trial_ends_at)
                            <div class="text-sm">
                                <p class="text-base-content/60">Trial ends</p>
                                <p class="font-medium {{ $vendor->isTrialExpired() ? 'text-error' : '' }}">
                                    {{ $vendor->trial_ends_at->format('d M Y') }}
                                    ({{ $vendor->isTrialExpired() ? 'Expired' : $vendor->trial_ends_at->diffForHumans() }})
                                </p>
                            </div>

                            @if($editingTrialDays)
                                <div class="flex items-end gap-2">
                                    <div class="form-control flex-1">
                                        <label class="label py-1"><span class="label-text text-xs">Days from now</span></label>
                                        <input type="number" wire:model="trialDays" class="input input-bordered input-sm" min="0" max="365" />
                                    </div>
                                    <button wire:click="updateTrialPeriod" class="btn btn-primary btn-sm">Save</button>
                                    <button wire:click="$set('editingTrialDays', false)" class="btn btn-ghost btn-sm">Cancel</button>
                                </div>
                            @else
                                <button wire:click="showTrialEdit" class="btn btn-ghost btn-xs">Adjust Trial</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Billing Config Card -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Billing</h2>
                    @if($vendor->billingConfig)
                        <dl class="mt-2 space-y-2 text-sm">
                            <div>
                                <dt class="text-base-content/60">Model</dt>
                                <dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $vendor->billingConfig->billing_model)) }}</dd>
                            </div>
                            @if($vendor->billingConfig->subscription_amount)
                                <div>
                                    <dt class="text-base-content/60">Subscription</dt>
                                    <dd>UGX {{ number_format($vendor->billingConfig->subscription_amount) }} / {{ $vendor->billingConfig->subscription_interval }}</dd>
                                </div>
                            @endif
                            @if($vendor->billingConfig->transaction_fee_percent)
                                <div>
                                    <dt class="text-base-content/60">Transaction Fee</dt>
                                    <dd>{{ $vendor->billingConfig->transaction_fee_percent }}%{{ $vendor->billingConfig->transaction_fee_flat ? ' + UGX ' . number_format($vendor->billingConfig->transaction_fee_flat) : '' }}</dd>
                                </div>
                            @endif
                            @if($vendor->billingConfig->commission_percent)
                                <div>
                                    <dt class="text-base-content/60">Commission</dt>
                                    <dd>{{ $vendor->billingConfig->commission_percent }}%</dd>
                                </div>
                            @endif
                            @if($vendor->billingConfig->next_billing_date)
                                <div>
                                    <dt class="text-base-content/60">Next Billing</dt>
                                    <dd>{{ $vendor->billingConfig->next_billing_date->format('d M Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <p class="text-sm text-base-content/50 mt-2">No billing configuration set.</p>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Summary</h2>
                    <div class="mt-2 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Branches</span>
                            <span class="font-medium">{{ $vendor->branches->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Users</span>
                            <span class="font-medium">{{ $vendor->users->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Created</span>
                            <span class="font-medium">{{ $vendor->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
