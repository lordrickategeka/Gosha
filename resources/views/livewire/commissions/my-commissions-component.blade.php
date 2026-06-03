<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">My Commissions</h1>
            <p class="text-base-content/60">View your earned commissions</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm text-base-content/60">Pending</h2>
                <p class="text-2xl font-bold text-warning">UGX {{ number_format($totals['pending'] ?? 0) }}</p>
                <p class="text-xs text-base-content/50">{{ $totals['pending_count'] ?? 0 }} pending</p>
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
                <h2 class="card-title text-sm text-base-content/60">This Month</h2>
                <p class="text-2xl font-bold text-primary">UGX {{ number_format($totals['this_month'] ?? 0) }}</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-sm text-base-content/60">Total Paid</h2>
                <p class="text-2xl font-bold text-success">UGX {{ number_format($totals['total_paid'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select wire:model="status" class="select select-bordered">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
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
                    <button wire:click="$refresh" class="btn btn-primary btn-sm">Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Commissions Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Rule</th>
                            <th class="text-right">Base Amount</th>
                            <th class="text-right">Commission</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Paid On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                            <tr>
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
                                <td class="text-sm">
                                    @if($commission->paid_at)
                                        {{ $commission->paid_at->format('d M Y') }}
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/50">
                                    No commissions found yet. Complete work orders to earn commissions!
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
</div>
