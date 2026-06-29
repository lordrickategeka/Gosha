<div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">RFQ Inbox</h1>
    <div class="space-y-3">
        @forelse ($rfqs as $rfq)
            <div class="card bg-base-100 shadow border border-base-300">
                <div class="card-body py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold">{{ $rfq->title ?: $rfq->reference }}</div>
                            <div class="text-sm text-base-content/60">
                                {{ $rfq->buyer?->name ?? 'Buyer' }} &middot; {{ $rfq->items->count() }} item(s)
                                @if ($rfq->closes_at) &middot; closes {{ $rfq->closes_at->diffForHumans() }} @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($rfq->has_quoted)
                                <span class="badge badge-success">Quoted</span>
                            @else
                                <a class="btn btn-sm btn-primary" href="{{ route('supplier.quotes.compose', $rfq) }}">Submit quote</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-base-content/60">No open RFQs to quote on right now.</p>
        @endforelse
    </div>
    {{ $rfqs->links() }}
</div>
