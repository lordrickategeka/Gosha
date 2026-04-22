<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('suppliers.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">{{ $supplier->name }}</h1>
                <span class="badge badge-{{ $supplier->is_active ? 'success' : 'error' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Items from this Supplier</h2>
                    @if($supplier->inventoryItems->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Item</th><th>SKU</th><th>Stock</th><th>Cost</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->inventoryItems as $item)
                                        <tr class="hover">
                                            <td><a href="{{ route('inventory.show', $item) }}" class="link link-hover">{{ $item->name }}</a></td>
                                            <td class="font-mono text-sm">{{ $item->sku ?? '-' }}</td>
                                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                            <td>UGX {{ number_format($item->cost_price) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-8 text-base-content/50">No items from this supplier</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Contact Info</h2>
                    <div class="space-y-3 text-sm">
                        @if($supplier->contact_person)
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span>{{ $supplier->contact_person }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <span>{{ $supplier->phone }}</span>
                        </div>
                        @if($supplier->email)
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <span>{{ $supplier->email }}</span>
                            </div>
                        @endif
                        @if($supplier->address)
                            <div class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/50 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>{{ $supplier->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Summary</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-base-content/60">Total Items</span><span class="font-medium">{{ $supplier->inventoryItems->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Added</span><span>{{ $supplier->created_at->format('d M Y') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
