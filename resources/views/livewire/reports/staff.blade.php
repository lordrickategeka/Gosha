<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Staff Performance</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Technician and attendant productivity</p>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <div class="gh-grid-4" style="grid-template-columns:repeat(5,1fr);">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Technicians</span>
            <span class="gh-stat__value">{{ number_format($this->summary['technicians']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Attendants</span>
            <span class="gh-stat__value">{{ number_format($this->summary['attendants']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Completed orders</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">{{ number_format($this->summary['completed_orders']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Completed washes</span>
            <span class="gh-stat__value" style="color:var(--gh-info);">{{ number_format($this->summary['completed_washes']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Commission</span>
            <span class="gh-stat__value">UGX {{ number_format($this->summary['commission_total']) }}</span>
        </div>
    </div>

    <div class="gh-grid-2">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px; display:flex; align-items:center; gap:8px;"><x-icon name="staff" class="gh-icon" /> Technicians</div>
            @if($this->technicianStats->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Name</th><th style="text-align:right;">Completed</th><th style="text-align:right;">Commission</th></tr></thead>
                        <tbody>
                            @foreach($this->technicianStats as $tech)
                                <tr>
                                    <td><a href="{{ route('users.show', $tech) }}" style="font-weight:600;">{{ $tech->name }}</a></td>
                                    <td style="text-align:right;"><span class="gh-badge gh-badge--success">{{ $tech->completed_orders }}</span></td>
                                    <td class="is-num">UGX {{ number_format($tech->total_commission ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:32px 0;">No technicians found</p>
            @endif
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px; display:flex; align-items:center; gap:8px;"><x-icon name="washBay" class="gh-icon" /> Wash Attendants</div>
            @if($this->washAttendantStats->count() > 0)
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Name</th><th style="text-align:right;">Completed</th><th style="text-align:right;">Commission</th></tr></thead>
                        <tbody>
                            @foreach($this->washAttendantStats as $attendant)
                                <tr>
                                    <td><a href="{{ route('users.show', $attendant) }}" style="font-weight:600;">{{ $attendant->name }}</a></td>
                                    <td style="text-align:right;"><span class="gh-badge gh-badge--info">{{ $attendant->completed_washes }}</span></td>
                                    <td class="is-num">UGX {{ number_format($attendant->total_commission ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="gh-muted" style="text-align:center; padding:32px 0;">No wash attendants found</p>
            @endif
        </div>
    </div>
</div>
