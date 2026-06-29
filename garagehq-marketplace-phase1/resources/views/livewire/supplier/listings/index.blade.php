<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">My Listings</h1>
        <button class="btn btn-primary" wire:click="openCreate">+ New Listing</button>
    </div>

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product / part no."
           class="input input-bordered w-full max-w-md" />

    @if ($showForm)
        <div class="card bg-base-100 shadow border border-base-300">
            <div class="card-body">
                <h2 class="card-title">{{ $editingId ? 'Edit' : 'New' }} Listing</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="form-control">
                        <span class="label-text">Catalog product</span>
                        <select class="select select-bordered" wire:model="catalog_product_id">
                            <option value="">— select —</option>
                            @foreach ($this->products as $p)
                                <option value="{{ $p->id }}">{{ $p->brand }} {{ $p->name }} ({{ $p->part_number }})</option>
                            @endforeach
                        </select>
                        @error('catalog_product_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </label>
                    <label class="form-control">
                        <span class="label-text">Supplier SKU</span>
                        <input class="input input-bordered" wire:model="supplier_sku" />
                    </label>
                    <label class="form-control">
                        <span class="label-text">Price ({{ config('marketplace.default_currency') }})</span>
                        <input type="number" step="0.01" class="input input-bordered" wire:model="price" />
                        @error('price') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </label>
                    <label class="form-control">
                        <span class="label-text">Stock qty</span>
                        <input type="number" class="input input-bordered" wire:model="stock_qty" />
                    </label>
                    <label class="form-control">
                        <span class="label-text">Min order qty</span>
                        <input type="number" class="input input-bordered" wire:model="min_order_qty" />
                    </label>
                    <label class="form-control">
                        <span class="label-text">Lead time (days)</span>
                        <input type="number" class="input input-bordered" wire:model="lead_time_days" />
                    </label>
                    <label class="form-control">
                        <span class="label-text">Condition</span>
                        <select class="select select-bordered" wire:model="condition">
                            <option value="new">New</option>
                            <option value="used">Used</option>
                            <option value="refurbished">Refurbished</option>
                        </select>
                    </label>
                    <label class="label cursor-pointer justify-start gap-3 mt-6">
                        <input type="checkbox" class="toggle toggle-primary" wire:model="is_active" />
                        <span class="label-text">Active</span>
                    </label>
                </div>
                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost" wire:click="$set('showForm', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>Product</th><th>SKU</th><th>Price</th><th>Stock</th><th>MOQ</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($listings as $listing)
                    <tr>
                        <td>{{ $listing->product?->brand }} {{ $listing->product?->name }}</td>
                        <td class="font-mono text-sm">{{ $listing->supplier_sku }}</td>
                        <td>{{ number_format($listing->price) }}</td>
                        <td>{{ $listing->stock_qty }}</td>
                        <td>{{ $listing->min_order_qty }}</td>
                        <td>
                            <span class="badge {{ $listing->is_active ? 'badge-success' : 'badge-ghost' }}">
                                {{ $listing->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <button class="btn btn-xs" wire:click="edit({{ $listing->id }})">Edit</button>
                            <button class="btn btn-xs btn-ghost" wire:click="toggle({{ $listing->id }})">Toggle</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-base-content/60 py-6">No listings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $listings->links() }}
</div>
