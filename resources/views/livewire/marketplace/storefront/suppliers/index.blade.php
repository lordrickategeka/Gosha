<div class="min-h-screen bg-base-100">
    <section class="bg-base-200 border-b border-base-300 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('storefront.index') }}" class="text-sm text-base-content/60 hover:text-primary">← Back to marketplace</a>
            <h1 class="text-3xl font-bold text-base-content mt-2">Verified Suppliers</h1>
            <p class="text-base-content/60 mt-1">Browse suppliers vetted by the GarageHQ platform team.</p>

            <div class="max-w-md mt-6">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search suppliers…"
                    class="input input-bordered w-full"
                />
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($suppliers->isEmpty())
            <div class="text-center py-16">
                <h3 class="text-lg font-medium text-base-content mb-2">No suppliers found</h3>
                <p class="text-base-content/60">Try a different search.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($suppliers as $supplier)
                    <a href="{{ route('storefront.suppliers.show', $supplier) }}" class="card bg-base-100 shadow-md hover:shadow-lg transition-shadow border border-base-200">
                        <div class="card-body p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold shrink-0">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-base-content truncate">{{ $supplier->name }}</h3>
                                    <span class="badge badge-success badge-xs">Verified</span>
                                </div>
                            </div>

                            @if($supplier->supplier_rating_count > 0)
                                <p class="text-sm text-base-content/70">★ {{ number_format($supplier->supplier_rating_avg, 1) }} ({{ $supplier->supplier_rating_count }} ratings)</p>
                            @else
                                <p class="text-sm text-base-content/50">No ratings yet</p>
                            @endif

                            <p class="text-sm text-base-content/60 mt-1">{{ $supplier->marketplace_listings_count }} active listing(s)</p>

                            @if($supplier->address)
                                <p class="text-xs text-base-content/50 mt-2 line-clamp-1">{{ $supplier->address }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>
