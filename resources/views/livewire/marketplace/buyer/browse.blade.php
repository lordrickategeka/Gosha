<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Marketplace</h1>
        <a class="btn btn-outline btn-sm" href="{{ route('marketplace.rfqs.create') }}">Request a quote (RFQ)</a>
    </div>

    @if ($focusProduct)
        <div class="alert alert-info">
            <span>Showing suppliers for <strong>{{ $focusProduct->brand }} {{ $focusProduct->name }}</strong>
            ({{ $focusProduct->part_number }}).
            <a class="link" href="{{ route('marketplace.browse') }}" wire:navigate>Clear filter</a></span>
        </div>
    @endif

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search part name, brand, or part number"
           class="input input-bordered w-full max-w-lg" />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($listings as $listing)
            <div class="card bg-base-100 shadow border border-base-300">
                <div class="card-body">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="card-title text-base">{{ $listing->product?->brand }} {{ $listing->product?->name }}</h2>
                        @if ($listing->supplier?->is_verified_supplier)
                            <span class="badge badge-success badge-sm">Verified</span>
                        @endif
                    </div>
                    <p class="text-sm text-base-content/60 font-mono">{{ $listing->product?->part_number }}</p>
                    <p class="text-sm">{{ $listing->supplier?->name }}</p>

                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-xl font-bold">{{ number_format($listing->price) }}</span>
                        <span class="text-sm text-base-content/60">{{ $listing->currency }}</span>
                    </div>
                    @if ($listing->priceTiers->isNotEmpty())
                        <p class="text-xs text-success">
                            Bulk: {{ number_format($listing->priceTiers->last()->unit_price) }} @ {{ $listing->priceTiers->last()->min_qty }}+
                        </p>
                    @endif
                    <p class="text-xs text-base-content/60">
                        Stock {{ $listing->stock_qty }} &middot; MOQ {{ $listing->min_order_qty }} &middot; lead {{ $listing->lead_time_days }}d
                    </p>

                    <div class="card-actions justify-end mt-3">
                        <button class="btn btn-sm btn-primary"
                                wire:click="buy({{ $listing->id }}, {{ $listing->min_order_qty }})"
                                wire:loading.attr="disabled">
                            Buy ({{ $listing->min_order_qty }})
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-base-content/60 col-span-full text-center py-10">No listings match.</p>
        @endforelse
    </div>
    {{ $listings->links() }}
</div>
