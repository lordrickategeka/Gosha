<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Inventory</h1>
            <p class="text-base-content/60">Manage parts and supplies</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning btn-sm">
                Low Stock ({{ $stats['low_stock'] }})
            </a>
            @can('create inventory')
            <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
            </a>
            @endcan
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Total Items</div>
            <div class="stat-value text-lg">{{ $stats['total'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Low Stock</div>
            <div class="stat-value text-lg text-warning">{{ $stats['low_stock'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Out of Stock</div>
            <div class="stat-value text-lg text-error">{{ $stats['out_of_stock'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Total Value</div>
            <div class="stat-value text-lg">UGX {{ number_format($stats['value']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search items..." class="input input-bordered input-sm" />
                <select wire:model.live="category" class="select select-bordered select-sm">
                    <option value="">All Categories</option>
                    @foreach($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="stockStatus" class="select select-bordered select-sm">
                    <option value="">All Stock Levels</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
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
                        <th class="text-right">Qty</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="hover">
                            <td>
                                <a href="{{ route('inventory.show', $item) }}" class="font-medium link link-hover">{{ $item->name }}</a>
                                @if($item->description)
                                    <p class="text-xs text-base-content/60 truncate max-w-xs">{{ $item->description }}</p>
                                @endif
                            </td>
                            <td class="font-mono text-sm">{{ $item->sku ?? '-' }}</td>
                            <td><span class="badge badge-ghost badge-sm">{{ $item->category?->name ?? '-' }}</span></td>
                            <td class="text-right font-medium">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="text-right">UGX {{ number_format($item->cost_price) }}</td>
                            <td class="text-right">UGX {{ number_format($item->selling_price) }}</td>
                            <td>
                                @if($item->quantity <= 0)
                                    <span class="badge badge-error badge-sm">Out of Stock</span>
                                @elseif($item->quantity <= $item->reorder_level)
                                    <span class="badge badge-warning badge-sm">Low Stock</span>
                                @else
                                    <span class="badge badge-success badge-sm">In Stock</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40">
                                        <li><a href="{{ route('inventory.show', $item) }}">View</a></li>
                                        @can('edit inventory')
                                            <li><a href="{{ route('inventory.edit', $item) }}">Edit</a></li>
                                        @endcan
                                        @can('adjust stock')
                                            <li><a href="{{ route('inventory.movements') }}?item={{ $item->id }}">Adjust Stock</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-8 text-base-content/50">No items found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="p-4 border-t border-base-200">{{ $items->links() }}</div>
        @endif
    </div>
</div>
