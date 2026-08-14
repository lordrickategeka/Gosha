<div class="gh-page">
    <div>
        <span class="gh-badge gh-badge--warning">Platform Admin</span>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:8px;">Billing Overview</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Platform revenue and subscription metrics</p>
    </div>

    <div class="gh-grid-3" style="grid-template-columns:repeat(6, minmax(0,1fr));">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Estimated MRR</span>
            <span class="gh-stat__value" style="color:var(--gh-success); font-size:16px;">UGX {{ number_format($this->stats['mrr']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Active subs</span>
            <span class="gh-stat__value" style="font-size:16px;">{{ $this->stats['active_subscriptions'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Active trials</span>
            <span class="gh-stat__value" style="color:var(--gh-warning); font-size:16px;">{{ $this->stats['active_trials'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Past due</span>
            <span class="gh-stat__value" style="color:var(--gh-error); font-size:16px;">{{ $this->stats['past_due'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total vendors</span>
            <span class="gh-stat__value" style="font-size:16px;">{{ $this->stats['total_vendors'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Outstanding</span>
            <span class="gh-stat__value" style="color:var(--gh-warning); font-size:16px;">UGX {{ number_format($this->stats['outstanding_balance']) }}</span>
        </div>
    </div>

    <div class="gh-grid-2">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Active Subscriptions by Plan</div>
            @if($this->vendorsByPlan->isEmpty())
                <p class="gh-muted" style="text-align:center; padding:24px 0; font-size:12px;">No active subscriptions yet</p>
            @else
                @php $total = $this->vendorsByPlan->sum() ?: 1; @endphp
                <div class="gh-stack" style="gap:12px;">
                    @foreach($this->vendorsByPlan as $planName => $count)
                        <div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:12.5px;">
                                <span style="font-weight:600;">{{ $planName }}</span>
                                <span class="gh-muted">{{ $count }} {{ Str::plural('vendor', $count) }}</span>
                            </div>
                            <div class="gh-meter"><div class="gh-meter__fill" style="width: {{ ($count / $total) * 100 }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Expiring Trials (7 days)</div>
            @if($this->expiringTrials->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Vendor</th><th>Plan</th><th>Expires</th><th></th></tr></thead>
                        <tbody>
                            @foreach($this->expiringTrials as $sub)
                                <tr style="{{ $sub->trial_ends_at->diffInDays(now()) <= 3 ? 'background:var(--gh-warning-bg);' : '' }}">
                                    <td><a href="{{ route('platform.vendors.show', $sub->vendor) }}" class="is-ref" style="font-weight:700;">{{ $sub->vendor->name }}</a></td>
                                    <td class="gh-muted">{{ $sub->plan->name }}</td>
                                    <td>{{ $sub->trial_ends_at->diffForHumans() }}</td>
                                    <td style="text-align:right;"><a href="{{ route('platform.vendors.show', $sub->vendor) }}" class="gh-btn gh-btn--primary gh-btn--sm">Manage</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:24px 0; font-size:12px;">No trials expiring soon</p>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Recent Invoices</div>
        @if($this->recentInvoices->count() > 0)
            <div class="gh-table-scroll">
                <table class="gh-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Vendor</th>
                            <th>Type</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->recentInvoices as $invoice)
                            <tr>
                                <td class="is-ref" style="font-family:monospace; font-size:11px;">{{ $invoice->invoice_number }}</td>
                                <td><a href="{{ route('platform.vendors.show', $invoice->vendor) }}" style="color:var(--gh-ink-body);">{{ $invoice->vendor->name }}</a></td>
                                <td><span class="gh-badge">{{ ucfirst($invoice->type) }}</span></td>
                                <td style="font-weight:700;">UGX {{ number_format($invoice->total) }}</td>
                                <td>
                                    <span class="gh-badge {{ match($invoice->status) {
                                        'paid' => 'gh-badge--success',
                                        'pending' => 'gh-badge--warning',
                                        'overdue' => 'gh-badge--error',
                                        default => '',
                                    } }}">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td style="{{ $invoice->isOverdue() ? 'color:var(--gh-error); font-weight:700;' : '' }}">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="gh-muted" style="text-align:center; padding:24px 0; font-size:12px;">No invoices yet</p>
        @endif
    </div>
</div>
