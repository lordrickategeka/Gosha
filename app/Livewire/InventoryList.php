<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\InventoryItem;

class InventoryList extends Component
{
    public $inventoryItems;

    public function mount()
    {
        $this->inventoryItems = InventoryItem::all();
    }

    public function deleteItem($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->delete();
        $this->inventoryItems = InventoryItem::all();
        session()->flash('message', 'Item deleted successfully.');
    }

    public function render()
    {
        return view('livewire.inventory-list', [
            'inventoryItems' => $this->inventoryItems,
        ]);
    }
}
