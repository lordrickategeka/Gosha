<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Expenses</h1>
            <p class="text-base-content/60">Track and manage all expenses</p>
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

    @if (session()->has('success'))
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm">
            <div class="stat-title text-xs">Today</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($stats['today']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm">
            <div class="stat-title text-xs">This Month</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($stats['this_month']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm">
            <div class="stat-title text-xs">This Year</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($stats['this_year']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm">
            <div class="stat-title text-xs">Pending Approval</div>
            <div class="stat-value text-lg text-warning">{{ $stats['pending_approval'] }}</div>
            <div class="stat-desc">Awaiting review</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Filters</h3>
                <div class="flex gap-2">
                    @if($search || $expenseType || $categoryId || $status || $dateFrom || $dateTo)
                        <button wire:click="clearFilters" class="btn btn-ghost btn-sm">Clear All</button>
                    @endif
                    <button wire:click="$toggle('showFilters')" class="btn btn-ghost btn-sm">
                        @if($showFilters)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        @endif
                    </button>
                </div>
            </div>

            @if($showFilters)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search..."
                        class="input input-bordered input-sm"
                    />

                    <select wire:model.live="expenseType" class="select select-bordered select-sm">
                        <option value="">All Types</option>
                        @foreach($expenseTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="categoryId" class="select select-bordered select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->full_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="status" class="select select-bordered select-sm">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <input type="date" wire:model.live="dateFrom" placeholder="From Date" class="input input-bordered input-sm" />
                    <input type="date" wire:model.live="dateTo" placeholder="To Date" class="input input-bordered input-sm" />
                </div>
            @endif
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Expense #</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $iteration => $expense)
                        <tr class="hover">
                            <td>{{ $iteration + 1 }}</td>
                            <td>
                                <a href="{{ route('expenses.view', $expense) }}" class="link link-primary font-mono text-sm">
                                    {{ $expense->expense_number }}
                                </a>
                            </td>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-ghost badge-sm">
                                    {{ ucfirst(str_replace('_', ' ', $expense->expense_type)) }}
                                </span>
                            </td>
                            <td>
                                <div class="max-w-xs truncate">{{ $expense->description }}</div>
                            </td>
                            <td>
                                @if($expense->category)
                                    {!! $expense->category->badge_html !!}
                                @else
                                    <span class="badge badge-ghost badge-sm">Uncategorized</span>
                                @endif
                            </td>
                            <td class="text-right font-medium text-error">
                                {{ $expense->formatted_total }}
                            </td>
                            <td>{!! $expense->status_badge !!}</td>
                            <td>{{ $expense->createdBy?->name ?? '-' }}</td>
                            <td class="text-right">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a href="{{ route('expenses.view', $expense) }}">View Details</a></li>
                                        @if($expense->isDraft() || $expense->isPendingApproval())
                                            @can('edit_expenses')
                                                <li><a href="{{ route('expenses.edit', $expense) }}">Edit</a></li>
                                            @endcan
                                        @endif
                                        @if($expense->isDraft() || $expense->isRejected())
                                            @can('delete_expenses')
                                                <li>
                                                    <a
                                                        wire:click="delete({{ $expense->id }})"
                                                        wire:confirm="Are you sure you want to delete this expense?"
                                                        class="text-error"
                                                    >
                                                        Delete
                                                    </a>
                                                </li>
                                            @endcan
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-base-content/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                                </svg>
                                <p>No expenses found</p>
                                @can('create_expenses')
                                    <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary mt-4">Create Your First Expense</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-base-200">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
