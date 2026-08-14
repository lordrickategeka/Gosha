<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Operations Report</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Work orders and wash statistics</p>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <div class="gh-grid-2">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Work Orders</div>
            <div class="gh-grid-3" style="margin-bottom:20px;">
                <div style="text-align:center; padding:14px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                    <p style="font-size:22px; font-weight:800;">{{ $this->workOrderStats['total'] }}</p>
                    <p class="gh-muted" style="font-size:11px;">Total</p>
                </div>
                <div style="text-align:center; padding:14px; background:var(--gh-success-bg); border-radius:var(--gh-radius);">
                    <p style="font-size:22px; font-weight:800; color:var(--gh-success);">{{ $this->workOrderStats['completed'] }}</p>
                    <p class="gh-muted" style="font-size:11px;">Completed</p>
                </div>
                <div style="text-align:center; padding:14px; background:var(--gh-warning-bg); border-radius:var(--gh-radius);">
                    <p style="font-size:22px; font-weight:800; color:var(--gh-warning);">{{ $this->workOrderStats['in_progress'] }}</p>
                    <p class="gh-muted" style="font-size:11px;">In progress</p>
                </div>
            </div>
            <div class="gh-eyebrow" style="margin-bottom:8px;">By type</div>
            <div class="gh-stack" style="gap:8px;">
                @foreach($this->workOrderStats['by_type'] as $type => $count)
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:12.5px;">{{ ucfirst($type) }}</span>
                        <span class="gh-badge">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Wash Orders</div>
            <div class="gh-grid-2" style="margin-bottom:20px;">
                <div style="text-align:center; padding:14px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                    <p style="font-size:22px; font-weight:800;">{{ $this->washOrderStats['total'] }}</p>
                    <p class="gh-muted" style="font-size:11px;">Total</p>
                </div>
                <div style="text-align:center; padding:14px; background:var(--gh-success-bg); border-radius:var(--gh-radius);">
                    <p style="font-size:22px; font-weight:800; color:var(--gh-success);">{{ $this->washOrderStats['completed'] }}</p>
                    <p class="gh-muted" style="font-size:11px;">Completed</p>
                </div>
            </div>
            <div class="gh-eyebrow" style="margin-bottom:8px;">By source</div>
            <div class="gh-stack" style="gap:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center;"><span style="font-size:12.5px;">Walk-in</span><span class="gh-badge">{{ $this->washOrderStats['walk_in'] }}</span></div>
                <div style="display:flex; justify-content:space-between; align-items:center;"><span style="font-size:12.5px;">Combo (from service)</span><span class="gh-badge gh-badge--primary">{{ $this->washOrderStats['combo'] }}</span></div>
            </div>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Daily Activity</div>
        @if($this->dailyVolume->count() > 0)
            <div class="gh-table-scroll">
                <table class="gh-table">
                    <thead><tr><th>Date</th><th style="text-align:right;">Work orders</th><th style="text-align:right;">Wash orders</th></tr></thead>
                    <tbody>
                        @foreach($this->dailyVolume as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day['date'])->format('D, d M') }}</td>
                                <td class="is-num">{{ $day['work_orders'] }}</td>
                                <td class="is-num">{{ $day['wash_orders'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="gh-muted" style="text-align:center; padding:32px 0;">No operations data for selected filters</p>
        @endif
    </div>
</div>
