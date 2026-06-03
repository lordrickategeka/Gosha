<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Commissions</h1>
            <p class="text-base-content/60">Manage and track staff commissions</p>
        </div>

        @if($this->canManageRules())
            <button wire:click="$toggle('showRuleModal')" class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Manage Rules
            </button>
        @endif
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm text-base-content/60">Pending</h2>
                <p class="text-2xl font-bold text-warning">UGX {{ number_format($totals['pending'] ?? 0) }}</p>
                <p class="text-xs text-base-content/50">{{ $totals['pending_count'] ?? 0 }} commissions</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm text-base-content/60">Approved</h2>
                <p class="text-2xl font-bold text-info">UGX {{ number_format($totals['approved'] ?? 0) }}</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm text-base-content/60">Paid This Month</h2>
                <p class="text-2xl font-bold text-success">UGX {{ number_format($totals['paid_month'] ?? 0) }}</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm text-base-content/60">Total Earned</h2>
                <p class="text-2xl font-bold">UGX {{ number_format($stats['total_paid'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Technician</span></label>
                    <select wire:model="user" class="select select-bordered">
                        <option value="">All Technicians</option>
                        @foreach($this->technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select wire:model="status" class="select select-bordered">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">From Date</span></label>
                    <input type="date" wire:model="dateFrom" class="input input-bordered" />
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text">To Date</span></label>
                    <input type="date" wire:model="dateTo" class="input input-bordered" />
                </div>

                <div class="form-control">
                    <label class="label">&nbsp;</label>
                    <div class="flex gap-2">
                        <button wire:click="$refresh" class="btn btn-primary btn-sm">Filter</button>
                        <button wire:click="resetFilter" class="btn btn-ghost btn-sm">Clear</button>
                    </div>
                </div>
            </div>

            @if($this->canApprove() || $this->canPay())
                <div class="flex gap-2 mt-4 pt-4 border-t border-base-300">
                    @if($this->canApprove())
                        <button wire:click="bulkApprove" class="btn btn-success btn-sm">
                            Bulk Approve Pending
                        </button>
                    @endif
                    @if($this->canPay())
                        <button wire:click="bulkMarkPaid" class="btn btn-warning btn-sm">
                            Bulk Mark as Paid
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Commissions Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Staff</th>
                            <th>Reference</th>
                            <th>Rule</th>
                            <th class="text-right">Base Amount</th>
                            <th class="text-right">Commission</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                            <tr>
                                <td class="font-mono text-xs">#{{ $commission->id }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                <span class="text-xs">{{ substr($commission->user?->name ?? 'U', 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <span>{{ $commission->user?->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @switch($commission->reference_type)
                                        @case('work_order')
                                            <a href="{{ route('work-orders.show', $commission->reference_id) }}" class="link link-primary text-xs">
                                                WO #{{ $commission->reference_id }}
                                            </a>
                                            @break
                                        @case('wash_order')
                                            <a href="{{ route('wash-orders.show', $commission->reference_id) }}" class="link link-primary text-xs">
                                                Wash #{{ $commission->reference_id }}
                                            </a>
                                            @break
                                        @case('invoice')
                                            <span class="text-xs">Inv #{{ $commission->reference_id }}</span>
                                            @break
                                        @default
                                            <span class="text-xs">{{ $commission->reference_type }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <span class="badge badge-ghost badge-sm">
                                        {{ $commission->commissionRule?->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-right text-sm">UGX {{ number_format($commission->base_amount) }}</td>
                                <td class="text-right font-bold">UGX {{ number_format($commission->commission_amount) }}</td>
                                <td>
                                    <span class="badge badge-{{ $commission->status_color }} badge-sm">
                                        {{ ucfirst($commission->status) }}
                                    </span>
                                </td>
                                <td class="text-sm">{{ $commission->created_at?->format('d M Y') }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        @if($commission->status === 'pending' && $this->canApprove())
                                            <button wire:click="approve({{ $commission->id }})"
                                                class="btn btn-xs btn-success"
                                                title="Approve">
                                                ✓
                                            </button>
                                        @endif
                                        @if(in_array($commission->status, ['pending', 'approved']) && $this->canPay())
                                            <button wire:click="markPaid({{ $commission->id }})"
                                                class="btn btn-xs btn-warning"
                                                title="Mark as Paid">
                                                💰
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8 text-base-content/50">
                                    No commissions found. Try adjusting your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                {{ $commissions->links() }}
            </div>
        </div>
    </div>

    <!-- Commission Rule Modal -->
    @if($showRuleModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">Commission Rules</h3>

                <!-- Existing Rules List -->
                <div class="mb-6">
                    <h4 class="font-medium mb-2">Existing Rules</h4>
                    @if($this->rules->isEmpty())
                        <p class="text-sm text-base-content/50">No rules defined yet.</p>
                    @else
                        <div class="overflow-x-auto max-h-60 overflow-y-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Type</th>
                                        <th>Applies To</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->rules as $rule)
                                        <tr>
                                            <td>{{ $rule->name }}</td>
                                            <td><span class="badge badge-ghost badge-sm">{{ $rule->role }}</span></td>
                                            <td>{{ $rule->type }}</td>
                                            <td>{{ $rule->applies_to }}</td>
                                            <td class="font-mono">{{ $rule->display_value }}</td>
                                            <td>
                                                <button wire:click="toggleRuleStatus({{ $rule->id }})"
                                                    class="badge badge-sm {{ $rule->is_active ? 'badge-success' : 'badge-error' }}">
                                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <button wire:click="openRuleModal({{ $rule->id }})"
                                                        class="btn btn-xs btn-ghost">Edit</button>
                                                    <button wire:click="deleteRule({{ $rule->id }})"
                                                        class="btn btn-xs btn-ghost text-error">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Add/Edit Rule Form -->
                <div class="border-t border-base-300 pt-4">
                    <h4 class="font-medium mb-2">{{ $selectedRule ? 'Edit Rule' : 'Add New Rule' }}</h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Rule Name</span></label>
                            <input type="text" wire:model="ruleName" class="input input-bordered" placeholder="e.g., Main Technician Rate" />
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Role</span></label>
                            <select wire:model="ruleRole" class="select select-bordered">
                                <option value="technician">Technician</option>
                                <option value="wash-attendant">Wash Attendant</option>
                                <option value="advisor">Advisor</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Type</span></label>
                            <select wire:model="ruleType" class="select select-bordered">
                                <option value="percentage">Percentage</option>
                                <option value="flat">Flat Rate</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Value</span></label>
                            <input type="number" step="0.01" wire:model="ruleValue" class="input input-bordered"
                                placeholder="{{ $ruleType === 'percentage' ? 'e.g., 10 for 10%' : 'e.g., 5000' }}" />
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Applies To</span></label>
                            <select wire:model="ruleAppliesTo" class="select select-bordered">
                                <option value="labor">Labor Charges</option>
                                <option value="parts">Parts Sales</option>
                                <option value="total">Total Invoice</option>
                                <option value="wash">Wash Services</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text">Minimum Threshold</span></label>
                            <input type="number" step="0.01" wire:model="ruleMinimumThreshold" class="input input-bordered" placeholder="Optional minimum" />
                        </div>
                    </div>

                    <div class="form-control mt-4">
                        <label class="label cursor-pointer">
                            <span class="label-text">Active</span>
                            <input type="checkbox" wire:model="ruleIsActive" class="toggle toggle-primary" />
                        </label>
                    </div>

                    <div class="form-control mt-4">
                        <label class="label"><span class="label-text">Description (optional)</span></label>
                        <textarea wire:model="ruleDescription" class="textarea textarea-bordered" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-action">
                    <button wire:click="closeRuleModal" class="btn btn-ghost">Close</button>
                    <button wire:click="saveRule" class="btn btn-primary">Save Rule</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeRuleModal"></div>
        </div>
    @endif
</div>
