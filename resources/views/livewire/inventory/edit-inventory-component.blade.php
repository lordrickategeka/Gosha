<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('inventory.show', $inventoryItem) }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit Item</h1>
            <p class="text-base-content/60">{{ $inventoryItem->name }}</p>
        </div>
    </div>

    <form wire:submit="save" class="max-w-3xl">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Item Name *</span></label>
                        <input type="text" wire:model="name" class="input input-bordered" />
                        @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">SKU</span></label>
                        <input type="text" wire:model="sku" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Category *</span></label>
                        <select wire:model="category_id" class="select select-bordered">
                            <option value="">Select category...</option>
                            @foreach($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Supplier</span></label>
                        <select wire:model="supplier_id" class="select select-bordered">
                            <option value="">Select supplier...</option>
                            @foreach($this->suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Unit</span></label>
                        <select wire:model="unit" class="select select-bordered">
                            <option value="pcs">Pieces</option>
                            <option value="liters">Liters</option>
                            <option value="kg">Kilograms</option>
                            <option value="meters">Meters</option>
                            <option value="sets">Sets</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Reorder Level *</span></label>
                        <input type="number" wire:model="reorder_level" class="input input-bordered" min="0" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Cost Price (UGX) *</span></label>
                        <input type="number" wire:model="cost_price" class="input input-bordered" min="0" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Selling Price (UGX) *</span></label>
                        <input type="number" wire:model="selling_price" class="input input-bordered" min="0" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Storage Location</span></label>
                        <input type="text" wire:model="location" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Status</span></label>
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" wire:model="is_active" class="toggle toggle-primary" />
                            <span class="label-text">{{ $is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Description</span></label>
                        <textarea wire:model="description" rows="2" class="textarea textarea-bordered"></textarea>
                    </div>
                </div>
                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('inventory.show', $inventoryItem) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>
