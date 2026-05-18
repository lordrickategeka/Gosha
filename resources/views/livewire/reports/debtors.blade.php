<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Debtors</h1>
            <p class="text-base-content/60">Customer receivables and aging analysis</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <label class="form-control">
                    <span class="label-text text-xs">As Of Date</span>
                    <input type="date" wire:model.live="asOfDate" class="input input-bordered" />
                </label>

                <label class="form-control">
                    <span class="label-text text-xs">Branch</span>
                    <select wire:model.live="branchId" class="select select-bordered">
                        <option value="">All Branches</option>
                        @foreach($this->availableBranches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs">Search Customer</span>
                    <input type="text" wire:model.live.debounce.300ms="search" class="input input-bordered" placeholder="Name, phone, or email" />
                </label>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Total Due</div>
            <div class="stat-value text-lg text-warning">UGX {{ number_format($this->totals['total_due']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Customers</div>
            <div class="stat-value text-lg">{{ $this->totals['customers'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Open Invoices</div>
            <div class="stat-value text-lg">{{ $this->totals['invoice_count'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">90+ Days</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($this->totals['days_90_plus']) }}</div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th class="text-right">Current</th>
                            <th class="text-right">1-30</th>
                            <th class="text-right">31-60</th>
                            <th class="text-right">61-90</th>
                            <th class="text-right">90+</th>
                            <th class="text-right">Total Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->rows as $row)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $row['customer_name'] }}</div>
                                    <div class="text-xs opacity-60">{{ $row['phone'] ?: $row['email'] ?: 'No contact' }}</div>
                                </td>
                                <td class="text-right">{{ number_format($row['buckets']['current']) }}</td>
                                <td class="text-right">{{ number_format($row['buckets']['days_1_30']) }}</td>
                                <td class="text-right">{{ number_format($row['buckets']['days_31_60']) }}</td>
                                <td class="text-right">{{ number_format($row['buckets']['days_61_90']) }}</td>
                                <td class="text-right text-error">{{ number_format($row['buckets']['days_90_plus']) }}</td>
                                <td class="text-right font-semibold">{{ number_format($row['total_due']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/50">No debtors for selected filters</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
