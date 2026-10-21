<div class="min-h-screen bg-base-100">
    <section class="bg-base-200 border-b border-base-300 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('storefront.suppliers.index') }}" class="text-sm text-base-content/60 hover:text-primary">← All suppliers</a>

            <div class="flex items-center gap-4 mt-3">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl shrink-0">
                    {{ strtoupper(substr($vendor->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-base-content">{{ $vendor->name }}</h1>
                        <span class="badge badge-success badge-sm">Verified</span>
                    </div>
                    @if($vendor->supplier_rating_count > 0)
                        <p class="text-sm text-base-content/70 mt-1">★ {{ number_format($vendor->supplier_rating_avg, 1) }} ({{ $vendor->supplier_rating_count }} ratings)</p>
                    @else
                        <p class="text-sm text-base-content/50 mt-1">No ratings yet</p>
                    @endif
                    @if($vendor->address)
                        <p class="text-sm text-base-content/60 mt-1">{{ $vendor->address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-lg font-semibold text-base-content mb-4">Listings from {{ $vendor->name }}</h2>

        @if($listings->isEmpty())
            <div class="text-center py-16">
                <h3 class="text-lg font-medium text-base-content mb-2">No active listings</h3>
                <p class="text-base-content/60">This supplier has no products listed right now.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($listings as $listing)
                    <div class="card bg-base-100 shadow-md hover:shadow-lg transition-shadow border border-base-200">
                        <div class="card-body p-4">
                            <h3 class="card-title text-sm line-clamp-2">{{ $listing->product?->brand }} {{ $listing->product?->name }}</h3>
                            <p class="text-xs font-mono text-base-content/60 mb-1">{{ $listing->product?->part_number }}</p>
                            <div class="flex items-baseline gap-1 mb-2">
                                <span class="text-xl font-bold text-primary">{{ number_format($listing->price) }}</span>
                                <span class="text-sm text-base-content/60">{{ $listing->currency }}</span>
                            </div>
                            <p class="text-xs text-base-content/50">
                                <span class="font-medium text-success">In Stock</span>
                                &middot; MOQ {{ $listing->min_order_qty }}
                            </p>
                            <div class="card-actions justify-end mt-3">
                                <a href="{{ route('marketplace.listings.show', $listing) }}" class="btn btn-sm btn-primary w-full">View Details</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">{{ $listings->links() }}</div>
        @endif
    </div>
</div>
