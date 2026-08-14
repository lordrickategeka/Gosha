<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Sales Report</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Revenue and payment analytics</p>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <x-reports.export-controls />

    <div class="gh-grid-4" style="grid-template-columns:repeat(5,1fr);">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total invoiced</span>
            <span class="gh-stat__value">UGX {{ number_format($this->stats['total_invoiced']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Collected</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">UGX {{ number_format($this->stats['total_collected']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Pending</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">UGX {{ number_format($this->stats['pending']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Invoices</span>
            <span class="gh-stat__value">{{ $this->stats['invoice_count'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Avg invoice</span>
            <span class="gh-stat__value">UGX {{ number_format($this->stats['avg_invoice']) }}</span>
        </div>
    </div>

    <div class="gh-grid-2">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Daily Revenue</div>
            @if($this->dailyRevenue->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Date</th><th style="text-align:right;">Revenue</th></tr></thead>
                        <tbody>
                            @foreach($this->dailyRevenue as $day)
                                <tr><td>{{ \Carbon\Carbon::parse($day->date)->format('D, d M') }}</td><td class="is-num">UGX {{ number_format($day->total) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:32px 0;">No data for selected period</p>
            @endif
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Payment Methods</div>
            @if($this->paymentMethods->count() > 0)
                <div class="gh-stack" style="gap:14px;">
                    @foreach($this->paymentMethods as $method)
                        @php $percentage = $this->stats['total_collected'] > 0 ? ($method->total / $this->stats['total_collected']) * 100 : 0; @endphp
                        <div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:12.5px;">
                                <span style="font-weight:600;">{{ ucfirst(str_replace('_', ' ', $method->payment_method)) }}</span>
                                <span>UGX {{ number_format($method->total) }} ({{ $method->count }})</span>
                            </div>
                            <div class="gh-meter"><div class="gh-meter__fill" style="width:{{ $percentage }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:32px 0;">No payments for selected period</p>
            @endif
        </div>
    </div>
</div>
