<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Invoices</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage billing and payments</p>
        </div>
        @can('create_invoices')
            <a href="{{ route('invoices.create') }}" class="gh-btn gh-btn--primary">+ New invoice</a>
        @endcan
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total invoiced</span>
            <span class="gh-stat__value">UGX {{ number_format($stats['total']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Paid</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">UGX {{ number_format($stats['paid']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Pending</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">UGX {{ number_format($stats['pending']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Overdue</span>
            <span class="gh-stat__value gh-stat__value--neg">UGX {{ number_format($stats['overdue']) }}</span>
        </div>
    </div>

    <div class="gh-table-toolbar">
        <span class="gh-hint">{{ $invoices->total() }} total records</span>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <label class="gh-search" style="width:190px;">⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search invoices…"></label>
            <select wire:model.live="status" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All status</option>
                <option value="sent">Pending</option>
                <option value="partial">Partially paid</option>
                <option value="paid">Paid</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <input type="date" wire:model.live="dateFrom" class="gh-input" style="padding:6px 10px; font-size:12px;">
            <input type="date" wire:model.live="dateTo" class="gh-input" style="padding:6px 10px; font-size:12px;">
            @if($search || $status || $dateFrom || $dateTo)
                <button wire:click="clearFilters" class="gh-btn gh-btn--sm">Clear</button>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr><th>Invoice #</th><th>Customer</th><th>Related order</th><th>Date</th><th>Due date</th><th style="text-align:right;">Total</th><th style="text-align:right;">Balance</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr data-href="{{ route('invoices.show', $invoice) }}">
                            <td class="is-ref"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->customer->name }}</td>
                            <td>
                                @if($invoice->workOrder)
                                    <a href="{{ route('work-orders.show', $invoice->workOrder) }}">{{ $invoice->workOrder->order_number }}</a>
                                @elseif($invoice->washOrder)
                                    <a href="{{ route('wash-orders.show', $invoice->washOrder) }}">{{ $invoice->washOrder->order_number }}</a>
                                @else
                                    <span class="gh-muted">—</span>
                                @endif
                            </td>
                            <td class="gh-muted">{{ $invoice->created_at->format('d M Y') }}</td>
                            <td style="{{ $invoice->isOverdue() ? 'color:var(--gh-error); font-weight:700;' : '' }}">{{ $invoice->due_date->format('d M Y') }}</td>
                            <td class="is-num">UGX {{ number_format($invoice->total) }}</td>
                            <td class="is-num" style="{{ $invoice->balance_due > 0 ? 'color:var(--gh-warning);' : 'color:var(--gh-success);' }}">UGX {{ number_format($invoice->balance_due) }}</td>
                            <td><span class="gh-badge {{ $invoice->status_color !== 'ghost' ? 'gh-badge--'.($invoice->status_color === 'accent' ? 'primary' : $invoice->status_color) : '' }}">{{ ucfirst($invoice->status) }}</span></td>
                            <td onclick="event.stopPropagation()">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-44 gh-card p-2 shadow-xl">
                                        <li><a href="{{ route('invoices.show', $invoice) }}">View</a></li>
                                        @if($invoice->balance_due > 0)
                                            <li><a href="{{ route('invoices.show', $invoice) }}?record_payment=1">Record payment</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No invoices found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="gh-pagination">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
