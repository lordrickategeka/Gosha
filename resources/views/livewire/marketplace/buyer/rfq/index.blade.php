<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">My RFQs</div>
        <a class="gh-btn gh-btn--primary gh-btn--sm" href="{{ route('marketplace.rfqs.create') }}">+ New RFQ</a>
    </div>
    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Reference</th><th>Title</th><th>Visibility</th><th>Status</th><th>Quotes</th><th></th></tr></thead>
                <tbody>
                    @forelse ($rfqs as $rfq)
                        <tr>
                            <td class="is-ref" style="font-family:monospace;">{{ $rfq->reference }}</td>
                            <td>{{ $rfq->title ?: '—' }}</td>
                            <td class="gh-muted">{{ ucfirst($rfq->visibility) }}</td>
                            <td><span class="gh-badge {{ str_replace('badge-', 'gh-badge--', $rfq->status->badge()) }}">{{ $rfq->status->label() }}</span></td>
                            <td class="gh-muted">{{ $rfq->quotes_count }}</td>
                            <td style="text-align:right;">
                                @if ($rfq->quotes_count > 0)
                                    <a class="gh-btn gh-btn--primary gh-btn--sm" href="{{ route('marketplace.quotes.compare', $rfq) }}">Compare quotes</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No RFQs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $rfqs->links() }}</div>
    </div>
</div>
