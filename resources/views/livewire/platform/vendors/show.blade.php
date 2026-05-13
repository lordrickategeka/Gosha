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

            <!-- Subscription Plan Card -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="card-title text-lg">Subscription Plan</h2>
                        <button wire:click="openAssignPlan" class="btn btn-primary btn-xs">
                            {{ $vendor->activeSubscription ? 'Change Plan' : 'Assign Plan' }}
                        </button>
                    </div>

                    @if($vendor->activeSubscription)
                        @php $sub = $vendor->activeSubscription; @endphp
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between items-start">
                                <span class="text-base-content/60">Plan</span>
                                <span class="font-semibold text-right">{{ $sub->plan->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Status</span>
                                <span class="badge badge-sm {{ match($sub->status) {
                                    'active' => 'badge-success',
                                    'trial' => 'badge-warning',
                                    'past_due' => 'badge-error',
                                    'cancelled' => 'badge-ghost',
                                    default => 'badge-ghost'
                                } }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Billing Model</span>
                                <span>{{ ucfirst(str_replace('_', ' ', $sub->plan->billing_model)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Effective Price</span>
                                <span class="font-medium">{{ $sub->plan->currency }} {{ number_format($sub->getEffectivePrice()) }}</span>
                            </div>
                            @if($sub->discount_percent > 0)
                                <div class="flex justify-between text-success">
                                    <span>Discount</span>
                                    <span>{{ $sub->discount_percent }}% — {{ $sub->discount_reason }}</span>
                                </div>
                            @endif
                            @if($sub->isTrialing())
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Trial Ends</span>
                                    <span class="{{ $sub->trial_ends_at->diffInDays(now()) <= 3 ? 'text-error' : '' }}">{{ $sub->trial_ends_at->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($sub->next_billing_date)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Next Billing</span>
                                    <span>{{ $sub->next_billing_date->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if(!$sub->isCancelled())
                                <div class="pt-2 border-t border-base-200">
                                    <button wire:click="cancelSubscription"
                                        wire:confirm="Cancel this vendor's subscription immediately?"
                                        class="btn btn-error btn-outline btn-xs w-full">
                                        Cancel Subscription
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-base-content/50">No active subscription. Assign a plan to start billing.</p>
                    @endif
                </div>
            </div>

            <!-- Legacy Billing Config (fallback display) -->
            @if($vendor->billingConfig && !$vendor->activeSubscription)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Legacy Billing Config</h2>
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
                        @if($vendor->billingConfig->commission_percent)
                            <div>
                                <dt class="text-base-content/60">Commission</dt>
                                <dd>{{ $vendor->billingConfig->commission_percent }}%</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
            @endif

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

    <!-- Assign Plan Modal -->
    @if($showAssignPlanModal)
    <div class="modal modal-open">
        <div class="modal-box app-modal-shell max-w-lg">
            <h3 class="font-bold text-lg mb-4">Assign Pricing Plan — {{ $vendor->name }}</h3>

            <div class="space-y-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Pricing Plan</span></label>
                    <select wire:model="selectedPlanId" class="select select-bordered">
                        <option value="">Select a plan...</option>
                        @foreach($this->plans as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->name }}
                                ({{ ucfirst($plan->billing_model) }} — {{ $plan->currency }} {{ number_format($plan->base_price) }}/{{ $plan->billing_cycle }})
                            </option>
                        @endforeach
                    </select>
                    @error('selectedPlanId') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Custom Price Override</span>
                        <span class="label-text-alt text-base-content/50">Leave blank to use plan default</span>
                    </label>
                    <input type="number" wire:model="customPrice" class="input input-bordered" placeholder="e.g. 90000" min="0" step="1000" />
                    @error('customPrice') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Discount %</span></label>
                        <input type="number" wire:model="discountPercent" class="input input-bordered" placeholder="0" min="0" max="100" step="1" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Discount Reason</span></label>
                        <input type="text" wire:model="discountReason" class="input input-bordered" placeholder="e.g. Early adopter" />
                    </div>
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer gap-3 justify-start">
                        <input type="checkbox" wire:model="waiveSetupFee" class="checkbox checkbox-sm" />
                        <span class="label-text">Waive setup fee</span>
                    </label>
                </div>
            </div>

            <div class="modal-action app-modal-actions">
                <button wire:click="closeAssignPlan" class="btn btn-ghost">Cancel</button>
                <button wire:click="assignPlan" wire:loading.attr="disabled" wire:target="assignPlan"
                    class="btn btn-primary">
                    <span wire:loading wire:target="assignPlan" class="loading loading-spinner loading-sm"></span>
                    Assign Plan
                </button>
            </div>
        </div>
        <div class="modal-backdrop app-modal-backdrop" wire:click="closeAssignPlan"></div>
    </div>
    @endif
</div>
