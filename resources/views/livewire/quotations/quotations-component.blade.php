<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Quotations</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Track all quotations and revisions</p>
    </div>

    <div class="gh-table-toolbar">
        <label class="gh-search" style="width:260px;">⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by quotation #, customer, or work order…"></label>
        <div style="display:flex; align-items:center; gap:8px;">
            <select wire:model.live="status" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="expired">Expired</option>
            </select>
            <span class="gh-hint">Show</span>
            <select wire:model.live="perPage" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr><th>Quotation</th><th>Customer</th><th>Work order</th><th>Created by</th><th>Status</th><th style="text-align:right;">Total</th><th>Valid until</th><th>Created</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        <tr data-href="{{ route('quotations.show', $quotation) }}">
                            <td class="is-ref">
                                <a href="{{ route('quotations.show', $quotation) }}">{{ $quotation->quotation_number }}</a>
                                @if($quotation->version > 1)
                                    <span class="gh-badge" style="margin-left:4px;">v{{ $quotation->version }}</span>
                                @endif
                                @if($quotation->parentQuotation)
                                    <div class="gh-muted" style="font-size:10.5px; margin-top:2px;">Revision of {{ $quotation->parentQuotation->quotation_number }}</div>
                                @endif
                            </td>
                            <td>{{ $quotation->customer?->name ?? '—' }}</td>
                            <td>
                                @if($quotation->workOrder)
                                    <a href="{{ route('work-orders.show', $quotation->workOrder) }}">{{ $quotation->workOrder->order_number }}</a>
                                @else
                                    <span class="gh-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $quotation->createdBy?->name ?? '—' }}</td>
                            <td><span class="gh-badge {{ $quotation->status_color !== 'ghost' ? 'gh-badge--'.($quotation->status_color === 'accent' ? 'primary' : $quotation->status_color) : '' }}">{{ ucfirst($quotation->status) }}</span></td>
                            <td class="is-num">UGX {{ number_format((float) $quotation->total) }}</td>
                            <td style="{{ $quotation->valid_until && $quotation->valid_until->isPast() ? 'color:var(--gh-error); font-weight:700;' : '' }}">{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</td>
                            <td class="gh-muted">{{ $quotation->created_at->format('d M Y') }}</td>
                            <td onclick="event.stopPropagation()">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-40 gh-card p-2 shadow-xl">
                                        <li><a href="{{ route('quotations.show', $quotation) }}">View</a></li>
                                        @can('edit_quotations')
                                            <li><a href="{{ route('quotations.edit', $quotation) }}">Edit</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No quotations found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
            <div class="gh-pagination">{{ $quotations->links() }}</div>
        @endif
    </div>
</div>
