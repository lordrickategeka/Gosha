<div class="gh-page">
    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Incoming Orders</div>

    <select class="gh-select" style="width:14rem;" wire:model.live="statusFilter">
        <option value="">All statuses</option>
        @foreach ($statuses as $s)
            <option value="{{ $s->value }}">{{ $s->label() }}</option>
        @endforeach
    </select>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>PO</th><th>Buyer</th><th>Total</th><th>Status</th><th>Payment</th><th></th></tr></thead>
                <tbody>
                    @forelse ($orders as $po)
                        <tr>
                            <td class="is-ref" style="font-family:monospace;">{{ $po->po_number }}</td>
                            <td>{{ $po->buyer?->name }}</td>
                            <td style="font-weight:700;">{{ number_format($po->total) }} {{ $po->currency }}</td>
                            <td><span class="gh-badge {{ str_replace('badge-', 'gh-badge--', $po->status->badge()) }}">{{ $po->status->label() }}</span></td>
                            <td><span class="gh-badge {{ str_replace('badge-', 'gh-badge--', $po->payment_status->badge()) }}">{{ $po->payment_status->label() }}</span></td>
                            <td style="text-align:right;">
                                @if ($po->status->value === 'sent')
                                    <button class="gh-btn gh-btn--primary gh-btn--sm" wire:click="accept({{ $po->id }})">Accept</button>
                                @endif
                                <a class="gh-btn gh-btn--sm" href="{{ route('supplier.orders.fulfill', $po) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $orders->links() }}</div>
    </div>
</div>
