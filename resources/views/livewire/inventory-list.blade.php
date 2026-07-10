<div>
    <h1 class="text-2xl font-bold mb-4">Inventory List</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
            <tr>
                <th class="border border-gray-300 px-4 py-2">ID</th>
                <th class="border border-gray-300 px-4 py-2">Name</th>
                <th class="border border-gray-300 px-4 py-2">Price</th>
                <th class="border border-gray-300 px-4 py-2">Quantity</th>
                <th class="border border-gray-300 px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventoryItems as $item)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->id }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->name }}</td>
                    <td class="border border-gray-300 px-4 py-2">${{ $item->price }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $item->quantity }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        <button wire:click="deleteItem({{ $item->id }})" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                        <a href="{{ route('inventory.edit', $item->id) }}" class="bg-primary text-primary-content px-2 py-1 rounded">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('inventory.create') }}" class="bg-green-500 text-white px-4 py-2 rounded mt-4 inline-block">Add New Item</a>
</div>
