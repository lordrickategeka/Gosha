<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Debtors</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Customer receivables and aging analysis</p>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-grid-4">
            <div class="gh-field">
                <span class="gh-label">As of date</span>
                <input type="date" wire:model.live="asOfDate" class="gh-input" style="width:100%;">
            </div>
            <div class="gh-field">
                <span class="gh-label">Branch</span>
                <select wire:model.live="branchId" class="gh-select" style="width:100%;">
                    <option value="">All branches</option>
                    @foreach($this->availableBranches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gh-field" style="grid-column:span 2;">
                <span class="gh-label">Search customer</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="gh-input" style="width:100%;" placeholder="Name, phone, or email">
            </div>
        </div>
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total due</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">UGX {{ number_format($this->totals['total_due']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Customers</span>
            <span class="gh-stat__value">{{ $this->totals['customers'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Open invoices</span>
            <span class="gh-stat__value">{{ $this->totals['invoice_count'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">90+ days</span>
            <span class="gh-stat__value gh-stat__value--neg">UGX {{ number_format($this->totals['days_90_plus']) }}</span>
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr><th>Customer</th><th style="text-align:right;">Current</th><th style="text-align:right;">1-30</th><th style="text-align:right;">31-60</th><th style="text-align:right;">61-90</th><th style="text-align:right;">90+</th><th style="text-align:right;">Total due</th></tr>
                </thead>
                <tbody>
                    @forelse($this->rows as $row)
                        <tr>
                            <td>
                                <div class="gh-cell-stack"><b>{{ $row['customer_name'] }}</b><span>{{ $row['phone'] ?: $row['email'] ?: 'No contact' }}</span></div>
                            </td>
                            <td class="is-num">{{ number_format($row['buckets']['current']) }}</td>
                            <td class="is-num">{{ number_format($row['buckets']['days_1_30']) }}</td>
                            <td class="is-num">{{ number_format($row['buckets']['days_31_60']) }}</td>
                            <td class="is-num">{{ number_format($row['buckets']['days_61_90']) }}</td>
                            <td class="is-num" style="color:var(--gh-error);">{{ number_format($row['buckets']['days_90_plus']) }}</td>
                            <td class="is-num" style="font-weight:700;">{{ number_format($row['total_due']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No debtors for selected filters</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
