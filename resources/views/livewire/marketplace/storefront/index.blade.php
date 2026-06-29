<div class="min-h-screen bg-base-100">
    <!-- Hero Section with Search -->
    <section class="relative bg-gradient-to-br from-primary via-primary/90 to-secondary overflow-hidden">
        <!-- Background pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 tracking-tight">
                    GarageHQ Marketplace
                </h1>
                <p class="text-lg md:text-xl text-white/80 mb-8 max-w-2xl mx-auto">
                    Quality auto parts from verified suppliers. Find the right parts for your workshop.
                </p>

                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search parts by name, brand, or part number..."
                            class="input input-bordered w-full pl-12 pr-4 py-4 text-lg bg-white/95 backdrop-blur shadow-xl"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters and Sort Bar -->
    <div class="bg-base-200 border-b border-base-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <!-- Category Filter -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-sm font-medium text-base-content/70 whitespace-nowrap">Category:</span>
                    <select
                        wire:model.live="category_id"
                        class="select select-bordered flex-1 sm:w-48"
                    >
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Dropdown -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-sm font-medium text-base-content/70 whitespace-nowrap">Sort by:</span>
                    <select
                        wire:model.live="sort_by"
                        class="select select-bordered flex-1 sm:w-40"
                    >
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                        <option value="name_asc">Name: A to Z</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($listings->isEmpty())
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-base-200 mb-4">
                    <svg class="h-8 w-8 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-base-content mb-2">No products found</h3>
                <p class="text-base-content/60 mb-6">Try adjusting your search or filter criteria</p>
                <button
                    wire:click="$set('search', ''); $set('category_id', null)"
                    class="btn btn-primary"
                >
                    Clear Filters
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($listings as $listing)
                    <div class="card bg-base-100 shadow-md hover:shadow-lg transition-shadow border border-base-200">
                        <!-- Product Image Placeholder -->
                        <figure class="h-48 bg-base-200 flex items-center justify-center">
                            @if($listing->product?->image_url)
                                <img src="{{ $listing->product->image_url }}" alt="{{ $listing->product?->name }}" class="w-full h-full object-cover" />
                            @else
                                <div class="text-center p-4">
                                    <svg class="h-12 w-12 mx-auto text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs text-base-content/40 mt-2 block">No Image</span>
                                </div>
                            @endif
                        </figure>

                        <div class="card-body p-4">
                            <!-- Header with badge -->
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="card-title text-sm line-clamp-2">
                                    {{ $listing->product?->brand }} {{ $listing->product?->name }}
                                </h3>
                                @if($listing->supplier?->is_verified_supplier)
                                    <span class="badge badge-success badge-xs shrink-0">Verified</span>
                                @endif
                            </div>

                            <!-- Part Number -->
                            <p class="text-xs font-mono text-base-content/60 mb-1">
                                {{ $listing->product?->part_number }}
                            </p>

                            <!-- Supplier -->
                            <p class="text-xs text-base-content/70 mb-3">
                                {{ $listing->supplier?->name }}
                            </p>

                            <!-- Price -->
                            <div class="flex items-baseline gap-1 mb-2">
                                <span class="text-xl font-bold text-primary">
                                    {{ number_format($listing->price) }}
                                </span>
                                <span class="text-sm text-base-content/60">{{ $listing->currency }}</span>
                            </div>

                            <!-- Bulk Tier Hint -->
                            @if($listing->priceTiers->isNotEmpty())
                                <p class="text-xs text-success mb-2">
                                    Bulk: {{ number_format($listing->priceTiers->last()->unit_price) }} @ {{ $listing->priceTiers->last()->min_qty }}+
                                </p>
                            @endif

                            <!-- Stock Info -->
                            <p class="text-xs text-base-content/50">
                                <span class="font-medium">
                                    @if($listing->stock_qty > 0)
                                        <span class="text-success">In Stock</span>
                                    @else
                                        <span class="text-error">Out of Stock</span>
                                    @endif
                                </span>
                                &middot; MOQ {{ $listing->min_order_qty }} &middot; Lead {{ $listing->lead_time_days }}d
                            </p>

                            <!-- CTA -->
                            <div class="card-actions justify-end mt-3">
                                <a href="{{ route('marketplace.listings.show', $listing) }}" class="btn btn-sm btn-primary w-full">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $listings->links() }}
            </div>
        @endif
    </div>

    <!-- CTA Section - Supplier Registration -->
    <section class="bg-base-200 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-base-content mb-4">Become a Supplier</h2>
            <p class="text-base-content/70 mb-8 max-w-2xl mx-auto">
                Join GarageHQ Marketplace and reach thousands of garages and workshops.
                List your products, receive RFQs, and grow your business.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                    Register as Supplier
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
                    Login to Dashboard
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-4">
                        <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-base-content mb-2">Verified Suppliers</h3>
                    <p class="text-base-content/60">All suppliers are vetted to ensure quality parts and reliable service.</p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-4">
                        <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-base-content mb-2">Fast RFQ Responses</h3>
                    <p class="text-base-content/60">Get quotes from multiple suppliers quickly through our RFQ system.</p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 mb-4">
                        <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-base-content mb-2">Competitive Pricing</h3>
                    <p class="text-base-content/60">Compare prices across suppliers and get bulk discounts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-neutral text-neutral-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-semibold">GarageHQ</span>
                </div>

                <div class="text-sm text-neutral-content/60">
                    &copy; {{ date('Y') }} GarageHQ. All rights reserved.
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('terms') }}" class="text-sm hover:text-primary transition-colors">Terms</a>
                    <a href="{{ route('policy') }}" class="text-sm hover:text-primary transition-colors">Privacy</a>
                </div>
            </div>
        </div>
    </footer>
</div>
