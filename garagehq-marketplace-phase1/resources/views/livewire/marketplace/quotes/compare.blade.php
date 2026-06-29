<div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">Compare Quotes — {{ $rfq->reference }}</h1>
    <p class="text-base-content/60">{{ $rfq->title }}</p>

    @if ($quotes->isEmpty())
        <p class="text-base-content/60">No submitted quotes yet.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($quotes as $index => $quote)
                <div class="card bg-base-100 shadow border {{ $index === 0 ? 'border-success' : 'border-base-300' }}">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <h2 class="card-title text-base">{{ $quote->supplier?->name }}</h2>
                            @if ($index === 0)<span class="badge badge-success">Lowest</span>@endif
                        </div>
                        <div class="text-2xl font-bold">{{ number_format($quote->total) }} {{ $quote->currency }}</div>
                        @if ($quote->valid_until)
                            <p class="text-xs text-base-content/60">Valid until {{ $quote->valid_until->toFormattedDateString() }}</p>
                        @endif
                        <ul class="text-sm mt-2 space-y-1">
                            @foreach ($quote->items as $qi)
                                <li class="flex justify-between">
                                    <span>{{ $qi->description ?? $qi->catalog_product_id }} × {{ $qi->qty }}</span>
                                    <span>{{ number_format($qi->line_total) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="card-actions justify-end mt-3">
                            <button class="btn btn-sm btn-primary"
                                    wire:click="award({{ $quote->id }})"
                                    wire:confirm="Award this quote? A draft PO will be created and other quotes rejected."
                                    wire:loading.attr="disabled">
                                Award
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
