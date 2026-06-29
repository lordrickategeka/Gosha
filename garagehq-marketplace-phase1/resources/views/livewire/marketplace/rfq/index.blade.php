<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">My RFQs</h1>
        <a class="btn btn-primary btn-sm" href="{{ route('marketplace.rfqs.create') }}">+ New RFQ</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Reference</th><th>Title</th><th>Visibility</th><th>Status</th><th>Quotes</th><th></th></tr></thead>
            <tbody>
                @forelse ($rfqs as $rfq)
                    <tr>
                        <td class="font-mono">{{ $rfq->reference }}</td>
                        <td>{{ $rfq->title ?: '—' }}</td>
                        <td>{{ ucfirst($rfq->visibility) }}</td>
                        <td><span class="badge {{ $rfq->status->badge() }}">{{ $rfq->status->label() }}</span></td>
                        <td>{{ $rfq->quotes_count }}</td>
                        <td class="text-right">
                            @if ($rfq->quotes_count > 0)
                                <a class="btn btn-xs btn-primary" href="{{ route('marketplace.quotes.compare', $rfq) }}">Compare quotes</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-base-content/60 py-6">No RFQs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $rfqs->links() }}
</div>
