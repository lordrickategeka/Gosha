<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">{{ $item->name }}</h1>
                <p class="text-base-content/60">{{ $item->sku ?? 'No SKU' }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            @can('adjust stock')
                <button wire:click="$set('showAdjustModal', true)" class="btn btn-primary">Adjust Stock</button>
            @endcan
            @can('edit inventory')
                <a href="{{ route('inventory.edit', $item) }}" class="btn btn-ghost">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Item Details</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-base-content/60">Category</p>
                            <p class="font-medium">{{ $item->category?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Supplier</p>
                            <p class="font-medium">{{ $item->supplier?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Unit</p>
                            <p class="font-medium">{{ $item->unit }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Cost Price</p>
                            <p class="font-medium">UGX {{ number_format($item->cost_price) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Selling Price</p>
                            <p class="font-medium">UGX {{ number_format($item->selling_price) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Location</p>
                            <p class="font-medium">{{ $item->location ?? '-' }}</p>
                        </div>
                    </div>
                    @if($item->description)
                        <div class="mt-4">
                            <p class="text-sm text-base-content/60">Description</p>
                            <p>{{ $item->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Movement History -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Recent Movements</h2>
                    @if($item->movements->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Date</th><th>Type</th><th>Qty</th><th>By</th><th>Notes</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($item->movements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('d M H:i') }}</td>
                                            <td><span class="badge badge-ghost badge-sm">{{ ucfirst($movement->type) }}</span></td>
                                            <td class="{{ $movement->quantity > 0 ? 'text-success' : 'text-error' }} font-medium">
                                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                            </td>
                                            <td>{{ $movement->user?->name ?? 'System' }}</td>
                                            <td class="text-sm text-base-content/60">{{ $movement->notes ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-4 text-base-content/50">No movements recorded</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Stock Status -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Stock Status</h2>
                    <div class="text-center py-4">
                        <p class="text-4xl font-bold {{ $item->quantity <= 0 ? 'text-error' : ($item->quantity <= $item->reorder_level ? 'text-warning' : 'text-success') }}">
                            {{ $item->quantity }}
                        </p>
                        <p class="text-base-content/60">{{ $item->unit }} in stock</p>

                        @if($item->quantity <= 0)
                            <div class="badge badge-error mt-2">Out of Stock</div>
                        @elseif($item->quantity <= $item->reorder_level)
                            <div class="badge badge-warning mt-2">Low Stock</div>
                        @else
                            <div class="badge badge-success mt-2">In Stock</div>
                        @endif
                    </div>
                    <div class="divider"></div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Reorder Level</span>
                            <span>{{ $item->reorder_level }} {{ $item->unit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Stock Value</span>
                            <span class="font-medium">UGX {{ number_format($item->quantity * $item->cost_price) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    @if($showAdjustModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Adjust Stock</h3>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Adjustment Type</span></label>
                    <select wire:model="adjustType" class="select select-bordered">
                        <option value="purchase">Purchase / Restock</option>
                        <option value="consumption">Consumption / Used</option>
                        <option value="adjustment">Manual Adjustment</option>
                        <option value="return">Customer Return</option>
                        <option value="transfer">Transfer Out</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Quantity</span></label>
                    <input type="number" wire:model="adjustQuantity" class="input input-bordered" min="1" />
                    <span class="label-text-alt">Current stock: {{ $item->quantity }} {{ $item->unit }}</span>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Notes</span></label>
                    <textarea wire:model="adjustNotes" class="textarea textarea-bordered" rows="2" placeholder="Reason for adjustment..."></textarea>
                </div>
                <div class="modal-action">
                    <button wire:click="$set('showAdjustModal', false)" class="btn btn-ghost">Cancel</button>
                    <button wire:click="adjustStock" class="btn btn-primary">Save Adjustment</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showAdjustModal', false)"></div>
        </div>
    @endif
</div>
