<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Stock Movements</h1>
            <p class="text-base-content/60">Track all inventory changes</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost">← Back to Inventory</a>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <select wire:model.live="item" class="select select-bordered select-sm">
                    <option value="">All Items</option>
                    @foreach($this->items as $i)
                        <option value="{{ $i->id }}">{{ $i->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="type" class="select select-bordered select-sm">
                    <option value="">All Types</option>
                    <option value="purchase">Purchase</option>
                    <option value="consumption">Consumption</option>
                    <option value="adjustment">Adjustment</option>
                    <option value="transfer">Transfer</option>
                    <option value="return">Return</option>
                </select>
                <input type="date" wire:model.live="dateFrom" class="input input-bordered input-sm" />
                <input type="date" wire:model.live="dateTo" class="input input-bordered input-sm" />
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th class="text-right">Quantity</th>
                        <th>Reference</th>
                        <th>By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr class="hover">
                            <td>{{ $movement->movement_date?->format('d M Y H:i') ?? $movement->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>
                                @if($movement->inventoryItem)
                                    <a href="{{ route('inventory.show', $movement->inventoryItem) }}" class="link link-hover">
                                        {{ $movement->inventoryItem->name }}
                                    </a>
                                @else
                                    <span class="text-base-content/40">Deleted Item</span>
                                @endif
                            </td>
                            <td><span class="badge badge-ghost badge-sm">{{ ucfirst($movement->movement_type) }}</span></td>
                            <td class="text-right font-bold {{ (($movement->quantity_change ?? 0) > 0) ? 'text-success' : 'text-error' }}">
                                {{ (($movement->quantity_change ?? 0) > 0) ? '+' : '' }}{{ $movement->quantity_change ?? 0 }}
                            </td>
                            <td>
                                @php $ref = $movement->reference(); @endphp
                                @if($ref && $movement->reference_type === 'work_order')
                                    <a href="{{ route('work-orders.show', $movement->reference_id) }}" class="link link-primary font-mono text-sm">
                                        {{ $ref->order_number }}
                                    </a>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>{{ $movement->performedBy?->name ?? 'System' }}</td>
                            <td class="text-sm text-base-content/60 max-w-xs truncate">{{ $movement->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-8 text-base-content/50">No movements found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="p-4 border-t border-base-200">{{ $movements->links() }}</div>
        @endif
    </div>
</div>
