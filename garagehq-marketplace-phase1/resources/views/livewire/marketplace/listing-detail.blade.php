<div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">{{ $listing->product?->brand }} {{ $listing->product?->name }}</h1>
    <div class="alert alert-info"><span>Full detail view is stubbed (compatibility table, tier breakdown, supplier profile).</span></div>
    <p>Price: {{ number_format($listing->price) }} {{ $listing->currency }}</p>
    <p>Supplier: {{ $listing->supplier?->name }}</p>
</div>
