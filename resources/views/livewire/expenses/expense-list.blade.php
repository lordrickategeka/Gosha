<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Expenses</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Track and manage all expenses</p>
        </div>
        @can('create_expenses')
            <a href="{{ route('expenses.create') }}" class="gh-btn gh-btn--primary">+ Add expense</a>
        @endcan
    </div>

    @if (session()->has('success'))
        <div class="gh-badge gh-badge--success" style="display:block; padding:10px 12px; font-size:12px;">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="gh-badge gh-badge--error" style="display:block; padding:10px 12px; font-size:12px;">{{ session('error') }}</div>
    @endif

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Today</span>
            <span class="gh-stat__value" style="color:var(--gh-error);">UGX {{ number_format($stats['today']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">This month</span>
            <span class="gh-stat__value" style="color:var(--gh-error);">UGX {{ number_format($stats['this_month']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">This year</span>
            <span class="gh-stat__value" style="color:var(--gh-error);">UGX {{ number_format($stats['this_year']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Pending approval</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">{{ $stats['pending_approval'] }}</span>
            <span class="gh-hint">Awaiting review</span>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div style="display:flex; align-items:center; justify-content:space-between; {{ $showFilters ? 'margin-bottom:14px;' : '' }}">
            <span style="font-weight:700; font-size:13.5px;">Filters</span>
            <div style="display:flex; gap:8px;">
                @if($search || $expenseType || $categoryId || $status || $dateFrom || $dateTo)
                    <button wire:click="clearFilters" class="gh-btn gh-btn--sm">Clear all</button>
                @endif
                <button wire:click="$toggle('showFilters')" class="gh-btn gh-btn--sm">{{ $showFilters ? '▲' : '▼' }}</button>
            </div>
        </div>

        @if($showFilters)
            <div class="gh-grid-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="gh-input" style="width:100%;">

                <select wire:model.live="expenseType" class="gh-select" style="width:100%;">
                    <option value="">All Types</option>
                    @foreach($expenseTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="categoryId" class="gh-select" style="width:100%;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->full_name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="status" class="gh-select" style="width:100%;">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <input type="date" wire:model.live="dateFrom" placeholder="From Date" class="gh-input" style="width:100%;">
                <input type="date" wire:model.live="dateTo" placeholder="To Date" class="gh-input" style="width:100%;">
            </div>
        @endif
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th class="is-index">#</th>
                        <th>Expense #</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $iteration => $expense)
                        <tr>
                            <td class="is-index">{{ $iteration + 1 }}</td>
                            <td><a href="{{ route('expenses.view', $expense) }}" class="is-ref" style="font-family:monospace;">{{ $expense->expense_number }}</a></td>
                            <td class="gh-muted">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td><span class="gh-badge">{{ ucfirst(str_replace('_', ' ', $expense->expense_type)) }}</span></td>
                            <td style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $expense->description }}</td>
                            <td>
                                @if($expense->category)
                                    {!! str_replace('badge badge-', 'gh-badge gh-badge--', $expense->category->badge_html) !!}
                                @else
                                    <span class="gh-badge">Uncategorized</span>
                                @endif
                            </td>
                            <td class="is-num" style="color:var(--gh-error);">{{ $expense->formatted_total }}</td>
                            <td>{!! str_replace('badge badge-', 'gh-badge gh-badge--', $expense->status_badge) !!}</td>
                            <td class="gh-muted">{{ $expense->createdBy?->name ?? '-' }}</td>
                            <td style="text-align:right;">
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300">
                                        <li><a href="{{ route('expenses.view', $expense) }}">View details</a></li>
                                        @if($expense->isDraft() || $expense->isPendingApproval())
                                            @can('edit_expenses')
                                                <li><a href="{{ route('expenses.edit', $expense) }}">Edit</a></li>
                                            @endcan
                                        @endif
                                        @if($expense->isDraft() || $expense->isRejected())
                                            @can('delete_expenses')
                                                <li><a wire:click="delete({{ $expense->id }})" wire:confirm="Are you sure you want to delete this expense?" style="color:var(--gh-error);">Delete</a></li>
                                            @endcan
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:48px 20px;">
                                <p style="color:var(--gh-ink-faint); font-size:13px;">No expenses found</p>
                                @can('create_expenses')
                                    <a href="{{ route('expenses.create') }}" class="gh-btn gh-btn--primary gh-btn--sm" style="margin-top:12px; display:inline-flex;">Create your first expense</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
