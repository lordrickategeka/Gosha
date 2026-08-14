<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Payments</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Payment history and records</p>
    </div>

    <div class="gh-grid-3">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Today</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">UGX {{ number_format($totals['today']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">This week</span>
            <span class="gh-stat__value">UGX {{ number_format($totals['week']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">This month</span>
            <span class="gh-stat__value">UGX {{ number_format($totals['month']) }}</span>
        </div>
    </div>

    <div class="gh-table-toolbar">
        <div class="gh-table-toolbar__filters">
            <label class="gh-search" style="width:190px;">⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search…"></label>
            <select wire:model.live="method" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All methods</option>
                <option value="cash">Cash</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
            <input type="date" wire:model.live="dateFrom" class="gh-input" style="padding:6px 10px; font-size:12px;">
            <input type="date" wire:model.live="dateTo" class="gh-input" style="padding:6px 10px; font-size:12px;">
            @if($search || $method || $dateFrom || $dateTo)
                <button wire:click="clearFilters" class="gh-btn gh-btn--sm">Clear</button>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Date</th><th>Customer</th><th>Invoice</th><th>Method</th><th>Reference</th><th style="text-align:right;">Amount</th><th>Received by</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="gh-muted">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td><a href="{{ route('customers.show', $payment->invoice->customer) }}">{{ $payment->invoice->customer->name }}</a></td>
                            <td>
                                @if($payment->invoice)
                                    <span class="is-ref"><a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a></span>
                                @else
                                    <span class="gh-muted">—</span>
                                @endif
                            </td>
                            <td><span class="gh-badge">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                            <td>{{ $payment->reference_number ?? '—' }}</td>
                            <td class="is-num" style="color:var(--gh-success);">UGX {{ number_format($payment->amount) }}</td>
                            <td>{{ $payment->receivedBy?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="gh-pagination">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
