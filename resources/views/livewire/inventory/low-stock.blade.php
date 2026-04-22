<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Low Stock Alert</h1>
            <p class="text-base-content/60">Items that need restocking</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost">← Back to Inventory</a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="stat bg-error/10 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Out of Stock</div>
            <div class="stat-value text-lg text-error">{{ $stats['out_of_stock'] }}</div>
        </div>
        <div class="stat bg-warning/10 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Low Stock</div>
            <div class="stat-value text-lg text-warning">{{ $stats['low_stock'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Restock Value</div>
            <div class="stat-value text-lg">UGX {{ number_format($stats['total_value_at_risk']) }}</div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search items..." class="input input-bordered w-full max-w-md" />
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th class="text-right">Current Stock</th>
                        <th class="text-right">Reorder Level</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="hover {{ $item->quantity <= 0 ? 'bg-error/5' : 'bg-warning/5' }}">
                            <td>
                                <a href="{{ route('inventory.show', $item) }}" class="font-medium link link-hover">{{ $item->name }}</a>
                            </td>
                            <td class="font-mono text-sm">{{ $item->sku ?? '-' }}</td>
                            <td><span class="badge badge-ghost badge-sm">{{ $item->category?->name ?? '-' }}</span></td>
                            <td>{{ $item->supplier?->name ?? '-' }}</td>
                            <td class="text-right font-bold {{ $item->quantity <= 0 ? 'text-error' : 'text-warning' }}">
                                {{ $item->quantity }} {{ $item->unit }}
                            </td>
                            <td class="text-right text-base-content/60">{{ $item->reorder_level }} {{ $item->unit }}</td>
                            <td>
                                @if($item->quantity <= 0)
                                    <span class="badge badge-error badge-sm">Out of Stock</span>
                                @else
                                    <span class="badge badge-warning badge-sm">Low Stock</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory.show', $item) }}?adjust=1" class="btn btn-primary btn-xs">Restock</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8">
                                <div class="text-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p>All items are well stocked!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="p-4 border-t border-base-200">{{ $items->links() }}</div>
        @endif
    </div>
</div>
