<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Inventory Report</h1>
            <p class="text-base-content/60">Stock levels, low inventory, and movement summaries</p>
        </div>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Items</div>
            <div class="stat-value text-lg">{{ number_format($this->overview['total_items']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">In Stock</div>
            <div class="stat-value text-lg text-success">{{ number_format($this->overview['in_stock']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Low Stock</div>
            <div class="stat-value text-lg text-warning">{{ number_format($this->overview['low_stock']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Out of Stock</div>
            <div class="stat-value text-lg text-error">{{ number_format($this->overview['out_of_stock']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4 col-span-2">
            <div class="stat-title text-xs">Stock Value</div>
            <div class="stat-value text-lg">UGX {{ number_format($this->overview['stock_value']) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Movement By Type</h2>
                @if($this->movementByType->count() > 0)
                    <div class="space-y-3">
                        @foreach($this->movementByType as $movement)
                            <div>
                                <div class="flex justify-between mb-1 text-sm">
                                    <span>{{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}</span>
                                    <span>{{ $movement->count }} records</span>
                                </div>
                                <progress class="progress progress-primary w-full" value="{{ min(($movement->count / max($this->overview['movement_count'], 1)) * 100, 100) }}" max="100"></progress>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-8 text-base-content/50">No inventory movement data for selected filters</p>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Low Stock Items</h2>
                @if($this->lowStockItems->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Item</th><th>Category</th><th class="text-right">Qty</th><th class="text-right">Reorder</th></tr>
                            </thead>
                            <tbody>
                                @foreach($this->lowStockItems as $item)
                                    <tr>
                                        <td>
                                            <div class="font-medium">{{ $item->name }}</div>
                                            <div class="text-xs text-base-content/50">{{ $item->sku }}</div>
                                        </td>
                                        <td>{{ $item->category?->name ?? 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->reorder_level, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-base-content/50">No low stock items</p>
                @endif
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Recent Stock Movements</h2>
            @if($this->recentMovements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Date</th><th>Item</th><th>Type</th><th>Branch</th><th>By</th><th class="text-right">Qty</th></tr>
                        </thead>
                        <tbody>
                            @foreach($this->recentMovements as $movement)
                                <tr>
                                    <td>{{ optional($movement->created_at)->format('d M Y') }}</td>
                                    <td>{{ $movement->inventoryItem?->name ?? 'N/A' }}</td>
                                    <td><span class="badge badge-ghost">{{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}</span></td>
                                    <td>{{ $movement->branch?->name ?? 'N/A' }}</td>
                                    <td>{{ $movement->performedBy?->name ?? 'System' }}</td>
                                    <td class="text-right">{{ number_format($movement->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center py-8 text-base-content/50">No stock movements found</p>
            @endif
        </div>
    </div>
</div>
