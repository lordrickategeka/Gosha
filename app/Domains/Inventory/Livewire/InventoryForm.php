<?php

namespace App\Domains\Inventory\Livewire;

use Livewire\Component;
use App\Domains\Inventory\Models\InventoryItem;
use Illuminate\Validation\Rule;

class InventoryForm extends Component
{
    public $inventoryItem;
    public $name;
    public $description;
    public $price;
    public $quantity;
    public $supplier_id;

    public function mount($inventoryItem = null)
    {
        if ($inventoryItem) {
            $this->inventoryItem = InventoryItem::findOrFail($inventoryItem);
            $this->name = $this->inventoryItem->name;
            $this->description = $this->inventoryItem->description;
            $this->price = $this->inventoryItem->price;
            $this->quantity = $this->inventoryItem->quantity;
            $this->supplier_id = $this->inventoryItem->supplier_id;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        if ($this->inventoryItem) {
            $this->inventoryItem->update([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'supplier_id' => $this->supplier_id,
            ]);
            session()->flash('message', 'Item updated successfully.');
        } else {
            InventoryItem::create([
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'quantity' => $this->quantity,
                'supplier_id' => $this->supplier_id,
            ]);
            session()->flash('message', 'Item created successfully.');
        }

        return redirect()->route('inventory.index');
    }

    public function render()
    {
        return view('livewire.inventory-form');
    }
}
