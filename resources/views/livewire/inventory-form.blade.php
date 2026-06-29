<div>
    <h1 class="text-2xl font-bold mb-4">{{ $inventoryItem ? 'Edit Inventory Item' : 'Create Inventory Item' }}</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="name" wire:model="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" wire:model="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
            <input type="number" id="price" wire:model="price" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
            <input type="number" id="quantity" wire:model="quantity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
            <select id="supplier_id" wire:model="supplier_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Select a supplier</option>
                @foreach (App\Domains\Inventory\Models\Supplier::all() as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
            @error('supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">{{ $inventoryItem ? 'Update' : 'Create' }}</button>
        </div>
    </form>
</div>
