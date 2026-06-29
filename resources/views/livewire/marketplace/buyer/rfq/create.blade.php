<div class="p-6 space-y-4 max-w-3xl">
    <h1 class="text-2xl font-bold">New RFQ</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="form-control">
            <span class="label-text">Title</span>
            <input class="input input-bordered" wire:model="title" placeholder="e.g. Brake parts for fleet" />
        </label>
        <label class="form-control">
            <span class="label-text">Visibility</span>
            <select class="select select-bordered" wire:model="visibility">
                <option value="open">Open (any supplier)</option>
                <option value="targeted">Targeted (invited suppliers)</option>
            </select>
        </label>
        <label class="form-control md:col-span-2">
            <span class="label-text">Notes</span>
            <textarea class="textarea textarea-bordered" wire:model="notes"></textarea>
        </label>
        <label class="form-control">
            <span class="label-text">Closes at</span>
            <input type="datetime-local" class="input input-bordered" wire:model="closes_at" />
            @error('closes_at') <span class="text-error text-sm">{{ $message }}</span> @enderror
        </label>
    </div>

    <div class="divider">Items</div>
    @error('items') <span class="text-error text-sm">{{ $message }}</span> @enderror

    @foreach ($items as $i => $row)
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
            <label class="form-control md:col-span-5">
                <span class="label-text">Catalog product</span>
                <select class="select select-bordered select-sm" wire:model="items.{{ $i }}.catalog_product_id">
                    <option value="">— or describe below —</option>
                    @foreach ($this->products as $p)
                        <option value="{{ $p->id }}">{{ $p->brand }} {{ $p->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control md:col-span-3">
                <span class="label-text">Free-text</span>
                <input class="input input-bordered input-sm" wire:model="items.{{ $i }}.description" />
                @error("items.$i.description") <span class="text-error text-xs">{{ $message }}</span> @enderror
            </label>
            <label class="form-control md:col-span-1">
                <span class="label-text">Qty</span>
                <input type="number" class="input input-bordered input-sm" wire:model="items.{{ $i }}.qty" />
            </label>
            <label class="form-control md:col-span-2">
                <span class="label-text">Target price</span>
                <input type="number" step="0.01" class="input input-bordered input-sm" wire:model="items.{{ $i }}.target_price" />
            </label>
            <div class="md:col-span-1">
                <button class="btn btn-sm btn-ghost text-error" wire:click="removeItem({{ $i }})">&#x2715;</button>
            </div>
        </div>
    @endforeach

    <button class="btn btn-sm btn-outline" wire:click="addItem">+ Add item</button>

    <div class="flex justify-end gap-2 pt-4">
        <a class="btn btn-ghost" href="{{ route('marketplace.browse') }}">Cancel</a>
        <button class="btn btn-primary" wire:click="save">Publish RFQ</button>
    </div>
</div>
