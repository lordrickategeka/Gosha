<div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">Supplier Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Active Listings</div>
            <div class="stat-value">{{ $activeListings }}</div>
            <a href="{{ route('supplier.listings.index') }}" class="stat-desc link">Manage</a>
        </div>
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Open RFQs</div>
            <div class="stat-value text-info">{{ $openRfqs }}</div>
            <a href="{{ route('supplier.quotes.inbox') }}" class="stat-desc link">Quote now</a>
        </div>
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Submitted Quotes</div>
            <div class="stat-value">{{ $pendingQuotes }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Incoming Orders</div>
            <div class="stat-value text-warning">{{ $incomingOrders }}</div>
            <a href="{{ route('supplier.orders.index') }}" class="stat-desc link">View</a>
        </div>
    </div>
</div>
