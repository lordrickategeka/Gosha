<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">My Commissions</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">View your earned commissions</p>
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Pending</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">UGX {{ number_format($totals['pending'] ?? 0) }}</span>
            <span class="gh-hint">{{ $totals['pending_count'] ?? 0 }} pending</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Approved</span>
            <span class="gh-stat__value" style="color:var(--gh-info);">UGX {{ number_format($totals['approved'] ?? 0) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">This month</span>
            <span class="gh-stat__value" style="color:var(--gh-primary);">UGX {{ number_format($totals['this_month'] ?? 0) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total paid</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">UGX {{ number_format($totals['total_paid'] ?? 0) }}</span>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-grid-2" style="grid-template-columns:repeat(4, 1fr);">
            <div class="gh-field">
                <span class="gh-label">Status</span>
                <select wire:model="status" class="gh-select" style="width:100%;">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div class="gh-field">
                <span class="gh-label">From date</span>
                <input type="date" wire:model="dateFrom" class="gh-input" style="width:100%;">
            </div>
            <div class="gh-field">
                <span class="gh-label">To date</span>
                <input type="date" wire:model="dateTo" class="gh-input" style="width:100%;">
            </div>
            <div style="display:flex; align-items:flex-end;">
                <button wire:click="$refresh" class="gh-btn gh-btn--primary gh-btn--sm">Filter</button>
            </div>
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Rule</th>
                        <th>Base Amount</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                        <tr>
                            <td>
                                @switch($commission->reference_type)
                                    @case('work_order')
                                        <a href="{{ route('work-orders.show', $commission->reference_id) }}" class="is-ref">WO #{{ $commission->reference_id }}</a>
                                        @break
                                    @case('wash_order')
                                        <a href="{{ route('wash-orders.show', $commission->reference_id) }}" class="is-ref">Wash #{{ $commission->reference_id }}</a>
                                        @break
                                    @case('invoice')
                                        <span>Inv #{{ $commission->reference_id }}</span>
                                        @break
                                    @default
                                        <span>{{ $commission->reference_type }}</span>
                                @endswitch
                            </td>
                            <td><span class="gh-badge">{{ $commission->commissionRule?->name ?? 'N/A' }}</span></td>
                            <td class="gh-muted">UGX {{ number_format($commission->base_amount) }}</td>
                            <td class="is-num">UGX {{ number_format($commission->commission_amount) }}</td>
                            <td><span class="gh-badge gh-badge--{{ $commission->status_color }}">{{ ucfirst($commission->status) }}</span></td>
                            <td class="gh-muted">{{ $commission->created_at?->format('d M Y') }}</td>
                            <td class="gh-muted">
                                @if($commission->paid_at)
                                    {{ $commission->paid_at->format('d M Y') }}
                                @else
                                    <span class="gh-hint">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No commissions found yet. Complete work orders to earn commissions!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $commissions->links() }}</div>
    </div>
</div>
