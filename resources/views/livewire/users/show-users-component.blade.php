<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('users.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="gh-sidebar__mark" style="width:48px; height:48px; border-radius:50%; font-size:16px;">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                <div>
                    <div style="font-size:20px; font-weight:700;">{{ $user->name }}</div>
                    <div style="display:flex; align-items:center; gap:6px; margin-top:4px;">
                        @foreach($user->roles as $role)
                            <span class="gh-badge gh-badge--primary">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                        @endforeach
                        <span class="gh-badge {{ $user->is_active ? 'gh-badge--success' : 'gh-badge--error' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @can('edit_users')
            <a href="{{ route('users.edit', $user) }}" class="gh-btn gh-btn--sm">Edit</a>
        @endcan
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <div class="gh-grid-4">
                <div class="gh-card gh-stat">
                    <span class="gh-stat__label">Work orders</span>
                    <span class="gh-stat__value">{{ $this->stats['work_orders'] }}</span>
                </div>
                <div class="gh-card gh-stat">
                    <span class="gh-stat__label">Wash orders</span>
                    <span class="gh-stat__value">{{ $this->stats['wash_orders'] }}</span>
                </div>
                <div class="gh-card gh-stat">
                    <span class="gh-stat__label">Completed today</span>
                    <span class="gh-stat__value" style="color:var(--gh-success);">{{ $this->stats['completed_today'] }}</span>
                </div>
                <div class="gh-card gh-stat">
                    <span class="gh-stat__label">Total commission</span>
                    <span class="gh-stat__value">UGX {{ number_format($this->stats['commissions_total']) }}</span>
                </div>
            </div>

            @if($user->commissions->count() > 0)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Recent Commissions</div>
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Date</th><th>Order</th><th>Type</th><th style="text-align:right;">Amount</th></tr></thead>
                            <tbody>
                                @foreach($user->commissions as $commission)
                                    <tr>
                                        <td class="gh-muted">{{ $commission->created_at->format('d M') }}</td>
                                        <td class="is-ref">{{ $commission->work_order_id ? 'WO' : 'WASH' }}-{{ $commission->work_order_id ?? $commission->wash_order_id }}</td>
                                        <td>{{ ucfirst($commission->type) }}</td>
                                        <td class="is-num" style="color:var(--gh-success);">UGX {{ number_format($commission->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Contact Info</div>
                <div class="gh-stack" style="gap:10px; font-size:12.5px;">
                    <div>{{ $user->email }}</div>
                    @if($user->phone)<div>{{ $user->phone }}</div>@endif
                </div>
            </div>

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Assigned Branches</div>
                @if($user->branches->count() > 0)
                    <div class="gh-stack" style="gap:8px;">
                        @foreach($user->branches as $branch)
                            <div style="padding:8px 10px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                                <p style="font-weight:600; font-size:12.5px;">{{ $branch->name }}</p>
                                <p class="gh-muted" style="font-size:11px;">{{ $branch->address }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="gh-muted">Access to all branches</p>
                @endif
            </div>

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Details</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Created</span><span>{{ $user->created_at->format('d M Y') }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Last login</span><span>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
