<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Inventory</h1>
            <p class="text-base-content/60">Manage parts and supplies</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Low Stock ({{ $stats['low_stock'] }})
            </a>
            @can('create_inventory')
            <button type="button" wire:click="openCreateModal" class="btn btn-primary rounded-lg px-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
            </button>
            @endcan
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm border border-base-300">
            <div class="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div class="stat-title text-xs">Total Items</div>
            <div class="stat-value text-2xl">{{ number_format($stats['total']) }}</div>
        </div>

        <div class="stat bg-base-100 rounded-lg shadow-sm border border-base-300">
            <div class="stat-figure text-accent">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
            </div>
            <div class="stat-title text-xs">Service Parts</div>
            <div class="stat-value text-2xl">{{ number_format($stats['service_parts']) }}</div>
        </div>

        <div class="stat bg-base-100 rounded-lg shadow-sm border border-base-300">
            <div class="stat-figure text-info">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
            </div>
            <div class="stat-title text-xs">Wash Supplies</div>
            <div class="stat-value text-2xl">{{ number_format($stats['wash_supplies']) }}</div>
        </div>

        <div class="stat bg-warning/10 rounded-lg shadow-sm border border-warning/30">
            <div class="stat-figure text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="stat-title text-xs">Low Stock</div>
            <div class="stat-value text-2xl text-warning">{{ number_format($stats['low_stock']) }}</div>
        </div>

        <div class="stat bg-error/10 rounded-lg shadow-sm border border-error/30">
            <div class="stat-figure text-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title text-xs">Out of Stock</div>
            <div class="stat-value text-2xl text-error">{{ number_format($stats['out_of_stock']) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="flex flex-row items-end gap-4 overflow-x-auto">
                {{-- Search --}}
                <div class="min-w-0 flex-1">
                    <label class="block text-sm font-medium mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by name, SKU, barcode, OEM number..."
                            class="input input-bordered w-full pl-10"
                        />
                    </div>
                </div>

                {{-- Filters --}}
                <div class="flex flex-row flex-nowrap items-end gap-3 shrink-0">
                    <div>
                        <label class="block text-sm font-medium mb-2">Type</label>
                        <select wire:model.live="itemType" class="select select-bordered w-40">
                            <option value="">All Types</option>
                            <option value="service_part">Service Parts</option>
                            <option value="wash_supply">Wash Supplies</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <select wire:model.live="category" class="select select-bordered w-44">
                            <option value="">All Categories</option>
                            @foreach($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->full_path }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Stock</label>
                        <select wire:model.live="stockStatus" class="select select-bordered w-36">
                            <option value="">All Stock</option>
                            <option value="low">Low Stock</option>
                            <option value="out">Out of Stock</option>
                            <option value="in_stock">In Stock</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Condition</label>
                        <select wire:model.live="condition" class="select select-bordered w-40">
                            <option value="">All Conditions</option>
                            <option value="new">New</option>
                            <option value="used">Used</option>
                            <option value="refurbished">Refurbished</option>
                        </select>
                    </div>

                    @if($search || $itemType || $category || $stockStatus || $condition)
                        <button wire:click="clearFilters" class="btn btn-ghost gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Items Grouped by Category --}}
    @if($items->count())
        <div class="space-y-4">
            @foreach($groupedItems as $categoryName => $categoryItems)
                <div class="card bg-base-100 shadow-sm border border-base-300">
                    {{-- Category Header --}}
                    <div class="card-body p-0">
                        <div class="collapse collapse-arrow">
                            <input type="checkbox" checked="checked" />
                            <div class="collapse-title bg-base-200/50 font-semibold flex items-center justify-between px-6">
                                <div class="flex items-center gap-3">
                                    <div class="badge badge-neutral badge-lg">{{ $categoryItems->count() }}</div>
                                    <span class="text-lg">{{ $categoryName }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base-content/60">Total Value:</span>
                                        <span class="font-bold">UGX {{ number_format($categoryItems->sum(fn($i) => $i->quantity * $i->cost_price)) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="badge badge-warning badge-sm">{{ $categoryItems->where('quantity', '<=', 'reorder_level')->count() }} Low</span>
                                        <span class="badge badge-error badge-sm">{{ $categoryItems->where('quantity', '<=', 0)->count() }} Out</span>
                                    </div>
                                </div>
                            </div>
                            <div class="collapse-content px-0 pt-0">
                                <div class="overflow-x-auto">
                                    <table class="table table-zebra">
                                        <thead class="bg-base-200">
                                            <tr>
                                                <th class="w-12">#</th>
                                                <th>Item Details</th>
                                                <th>SKU / Codes</th>
                                                <th class="text-center">Stock</th>
                                                <th class="text-right">Cost Price</th>
                                                <th class="text-right">Selling Price</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($categoryItems as $item)
                                                <tr class="hover">
                                                    <td class="font-mono text-xs text-base-content/60">{{ $loop->iteration }}</td>

                                                    {{-- Item Details --}}
                                                    <td>
                                                        <div class="flex items-start gap-3">
                                                            @if($item->image_path)
                                                                <div class="avatar">
                                                                    <div class="w-12 h-12 rounded-lg">
                                                                        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}" />
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="avatar placeholder">
                                                                    <div class="w-12 h-12 rounded-lg bg-neutral text-neutral-content">
                                                                        <span class="text-xl">{{ strtoupper(substr($item->name, 0, 1)) }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            <div class="flex-1 min-w-0">
                                                                <a href="{{ route('inventory.show', $item) }}" class="font-semibold link link-hover">
                                                                    {{ $item->name }}
                                                                </a>
                                                                @if($item->brand)
                                                                    <span class="badge badge-sm badge-ghost ml-2">{{ $item->brand }}</span>
                                                                @endif
                                                                @if($item->description)
                                                                    <p class="text-xs text-base-content/60 truncate max-w-md mt-1">{{ $item->description }}</p>
                                                                @endif
                                                                <div class="flex gap-2 mt-1">
                                                                    @if($item->position)
                                                                        <span class="badge badge-xs">{{ ucfirst($item->position) }}</span>
                                                                    @endif
                                                                    @if($item->condition)
                                                                        <span class="badge badge-xs badge-{{ $item->condition === 'new' ? 'success' : 'warning' }}">
                                                                            {{ ucfirst($item->condition) }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    {{-- SKU / Codes --}}
                                                    <td>
                                                        <div class="space-y-1">
                                                            @if($item->sku)
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-xs text-base-content/60">SKU:</span>
                                                                    <code class="text-xs bg-base-200 px-2 py-0.5 rounded">{{ $item->sku }}</code>
                                                                </div>
                                                            @endif
                                                            @if($item->oem_number)
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-xs text-base-content/60">OEM:</span>
                                                                    <code class="text-xs bg-base-200 px-2 py-0.5 rounded">{{ $item->oem_number }}</code>
                                                                </div>
                                                            @endif
                                                            @if($item->barcode)
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-xs text-base-content/60">Barcode:</span>
                                                                    <code class="text-xs bg-base-200 px-2 py-0.5 rounded">{{ $item->barcode }}</code>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    {{-- Stock --}}
                                                    <td class="text-center">
                                                        <div class="flex flex-col items-center gap-1">
                                                            <span class="font-bold text-lg">{{ number_format($item->quantity, 2) }}</span>
                                                            <span class="text-xs text-base-content/60">{{ $item->unit }}</span>
                                                            @if($item->quantity <= $item->reorder_level && $item->quantity > 0)
                                                                <div class="text-xs text-warning flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                                                    </svg>
                                                                    Reorder at {{ $item->reorder_level }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    {{-- Cost Price --}}
                                                    <td class="text-right">
                                                        <div class="font-semibold">UGX {{ number_format($item->cost_price) }}</div>
                                                        <div class="text-xs text-base-content/60">Total: {{ number_format($item->quantity * $item->cost_price) }}</div>
                                                    </td>

                                                    {{-- Selling Price --}}
                                                    <td class="text-right">
                                                        <div class="font-semibold">UGX {{ number_format($item->selling_price) }}</div>
                                                        @if($item->selling_price > 0)
                                                            @php
                                                                $margin = (($item->selling_price - $item->cost_price) / $item->selling_price) * 100;
                                                            @endphp
                                                            <div class="text-xs {{ $margin > 0 ? 'text-success' : 'text-error' }}">
                                                                {{ number_format($margin, 1) }}% margin
                                                            </div>
                                                        @endif
                                                    </td>

                                                    {{-- Status --}}
                                                    <td class="text-center">
                                                        @if($item->quantity <= 0)
                                                            <div class="badge badge-error gap-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                                Out of Stock
                                                            </div>
                                                        @elseif($item->quantity <= $item->reorder_level)
                                                            <div class="badge badge-warning gap-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                                Low Stock
                                                            </div>
                                                        @else
                                                            <div class="badge badge-success gap-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                In Stock
                                                            </div>
                                                        @endif

                                                        @if($item->expiry_date)
                                                            @php
                                                                $daysToExpiry = now()->diffInDays($item->expiry_date, false);
                                                            @endphp
                                                            @if($daysToExpiry < 0)
                                                                <div class="badge badge-error badge-sm mt-1">Expired</div>
                                                            @elseif($daysToExpiry <= 30)
                                                                <div class="badge badge-warning badge-sm mt-1">{{ $daysToExpiry }}d to expiry</div>
                                                            @endif
                                                        @endif
                                                    </td>

                                                    {{-- Actions --}}
                                                    <td class="text-right">
                                                        <div class="dropdown dropdown-end">
                                                            <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                                                </svg>
                                                            </label>
                                                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300">
                                                                <li>
                                                                    <a href="{{ route('inventory.show', $item) }}">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                        </svg>
                                                                        View Details
                                                                    </a>
                                                                </li>
                                                                @can('edit_inventory')
                                                                    <li>
                                                                        <a href="{{ route('inventory.edit', $item) }}">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                            </svg>
                                                                            Edit Item
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                                <div class="divider my-0"></div>
                                                                @can('adjust_stock')
                                                                    <li>
                                                                        <a wire:click="$dispatch('openAdjustModal', { itemId: {{ $item->id }} })">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                                            </svg>
                                                                            Adjust Stock
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="{{ route('inventory.movements') }}?item={{ $item->id }}">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                                            </svg>
                                                                            View History
                                                                        </a>
                                                                    </li>
                                                                @endcan
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $items->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="text-lg font-semibold mt-4">No inventory items found</h3>
                <p class="text-base-content/60">
                    @if($search || $itemType || $category || $stockStatus)
                        Try adjusting your filters or search terms
                    @else
                        Get started by adding your first inventory item
                    @endif
                </p>
                <div class="card-actions justify-center mt-6">
                    @if($search || $itemType || $category || $stockStatus)
                        <button wire:click="clearFilters" class="btn btn-primary">Clear Filters</button>
                    @else
                        @can('create_inventory')
                            <button type="button" wire:click="openCreateModal" class="btn btn-primary rounded-lg px-5">Add First Item</button>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($showCreateModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box app-modal-shell max-w-4xl">
                <h3 class="font-bold text-lg mb-4">Add Inventory Item</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control sm:col-span-2">
                        <label class="block text-sm font-medium mb-2">Item Name *</label>
                        <input type="text" wire:model="createName" class="input input-bordered" />
                        @error('createName') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Item Type *</label>
                        <select wire:model="createItemType" class="select select-bordered">
                            <option value="service_part">Service Part</option>
                            <option value="wash_supply">Wash Supply</option>
                        </select>
                        @error('createItemType') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Category *</label>
                        <select wire:model="createCategoryId" class="select select-bordered">
                            <option value="">Select category...</option>
                            @foreach($this->createCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('createCategoryId') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">SKU</label>
                        <input type="text" value="{{ $createSku ?: 'Select a category to generate SKU' }}" class="input input-bordered" readonly />
                        <span class="label-text-alt text-base-content/60 mt-1">Generated automatically from the selected category.</span>
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Barcode</label>
                        <input type="text" wire:model="createBarcode" class="input input-bordered" />
                        @error('createBarcode') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Supplier</label>
                        <select wire:model="createSupplierId" class="select select-bordered">
                            <option value="">Select supplier...</option>
                            @foreach($this->suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('createSupplierId') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Brand</label>
                        <input type="text" wire:model="createBrand" class="input input-bordered" />
                        @error('createBrand') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Unit *</label>
                        <select wire:model="createUnit" class="select select-bordered">
                            <option value="pcs">Pieces</option>
                            <option value="liters">Liters</option>
                            <option value="kg">Kilograms</option>
                            <option value="meters">Meters</option>
                            <option value="sets">Sets</option>
                        </select>
                        @error('createUnit') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Condition *</label>
                        <select wire:model="createCondition" class="select select-bordered">
                            <option value="new">New</option>
                            <option value="used">Used</option>
                            <option value="refurbished">Refurbished</option>
                            <option value="reconditioned">Reconditioned</option>
                        </select>
                        @error('createCondition') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Initial Quantity *</label>
                        <input type="number" wire:model="createQuantity" class="input input-bordered" min="0" step="0.01" />
                        @error('createQuantity') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Reorder Level *</label>
                        <input type="number" wire:model="createReorderLevel" class="input input-bordered" min="0" step="0.01" />
                        @error('createReorderLevel') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Cost Price (UGX) *</label>
                        <input type="number" wire:model="createCostPrice" class="input input-bordered" min="0" step="0.01" />
                        @error('createCostPrice') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Selling Price (UGX) *</label>
                        <input type="number" wire:model="createSellingPrice" class="input input-bordered" min="0" step="0.01" />
                        @error('createSellingPrice') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="block text-sm font-medium mb-2">Storage Location</label>
                        <input type="text" wire:model="createLocation" class="input input-bordered" />
                        @error('createLocation') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control sm:col-span-2">
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea wire:model="createDescription" rows="3" class="textarea textarea-bordered"></textarea>
                        @error('createDescription') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-action app-modal-actions">
                    <button type="button" wire:click="closeCreateModal" class="btn btn-ghost">Cancel</button>
                    <button type="button" wire:click="saveInventoryItem" class="btn btn-primary rounded-lg px-5" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveInventoryItem">Create Item</span>
                        <span wire:loading wire:target="saveInventoryItem" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="closeCreateModal"></div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Add method to clear filters
    Livewire.on('filtersCleared', () => {
        // Optional: Show toast notification
    });
</script>
@endpush
