<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Expenses</h1>
            <p class="text-base-content/60">Track business expenses</p>
        </div>
        @can('create_expenses')
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Expense
        </a>
        @endcan
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Today</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($totals['today']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">This Month</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($totals['month']) }}</div>
        </div>
    </div>

    <!-- Filters (in table header) -->
    <div class="card bg-base-100 shadow-sm mb-4">
        <div class="flex flex-wrap items-center gap-2 p-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="input input-bordered input-sm w-40" />
            <select wire:model.live="category" class="select select-bordered select-sm w-36">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="dateFrom" class="input input-bordered input-sm w-36" />
            <input type="date" wire:model.live="dateTo" class="input input-bordered input-sm w-36" />
            @if($search || $category || $dateFrom || $dateTo)
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
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr class="hover">
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td><span class="badge badge-ghost badge-sm">{{ $expense->category?->name ?? '-' }}</span></td>
                            <td class="text-right font-medium text-error">UGX {{ number_format($expense->amount) }}</td>
                            <td>
                                <span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'pending' ? 'warning' : 'error') }} badge-sm">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td>{{ $expense->recordedBy?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-base-content/50">No expenses found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="p-4 border-t border-base-200">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
