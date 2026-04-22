<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Livewire\Component;

class EditInventoryComponent extends Component
{
    public InventoryItem $inventoryItem;

    public $name = '';
    public $sku = '';
    public $description = '';
    public $category_id = '';
    public $supplier_id = '';
    public $unit = 'pcs';
    public $cost_price = 0;
    public $selling_price = 0;
    public $reorder_level = 5;
    public $location = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'nullable|string|max:50',
        'category_id' => 'required|exists:inventory_categories,id',
        'unit' => 'required|string|max:20',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'reorder_level' => 'required|integer|min:0',
    ];

    public function mount(InventoryItem $inventoryItem)
    {
        $this->inventoryItem = $inventoryItem;
        $this->name = $inventoryItem->name;
        $this->sku = $inventoryItem->sku;
        $this->description = $inventoryItem->description;
        $this->category_id = $inventoryItem->category_id;
        $this->supplier_id = $inventoryItem->supplier_id;
        $this->unit = $inventoryItem->unit;
        $this->cost_price = $inventoryItem->cost_price;
        $this->selling_price = $inventoryItem->selling_price;
        $this->reorder_level = $inventoryItem->reorder_level;
        $this->location = $inventoryItem->location;
        $this->is_active = $inventoryItem->is_active;
    }

    public function getCategoriesProperty()
    {
        return InventoryCategory::orderBy('name')->get();
    }

    public function getSuppliersProperty()
    {
        return Supplier::where('is_active', true)->orderBy('name')->get();
    }

    public function save()
    {
        $this->validate();

        $this->inventoryItem->update([
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id ?: null,
            'unit' => $this->unit,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'reorder_level' => $this->reorder_level,
            'location' => $this->location,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Inventory item updated.');
        return redirect()->route('inventory.show', $this->inventoryItem);
    }

    public function render()
    {
        return view('livewire.inventory.edit-inventory-component')
            ->layout('components.layouts.app', ['title' => 'Edit ' . $this->inventoryItem->name]);
    }
}
