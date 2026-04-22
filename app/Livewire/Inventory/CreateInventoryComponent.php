<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Livewire\Component;

class CreateInventoryComponent extends Component
{
    public $name = '';
    public $sku = '';
    public $description = '';
    public $category_id = '';
    public $supplier_id = '';
    public $unit = 'pcs';
    public $quantity = 0;
    public $cost_price = 0;
    public $selling_price = 0;
    public $reorder_level = 5;
    public $location = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'sku' => 'nullable|string|max:50',
        'category_id' => 'required|exists:inventory_categories,id',
        'unit' => 'required|string|max:20',
        'quantity' => 'required|integer|min:0',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'reorder_level' => 'required|integer|min:0',
    ];

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

        $item = InventoryItem::create([
            'vendor_id' => auth()->user()->vendor_id,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id ?: null,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'reorder_level' => $this->reorder_level,
            'location' => $this->location,
            'is_active' => true,
        ]);

        if ($this->quantity > 0) {
            $item->movements()->create([
                'branch_id' => session('current_branch_id'),
                'user_id' => auth()->id(),
                'type' => 'purchase',
                'quantity' => $this->quantity,
                'unit_cost' => $this->cost_price,
                'notes' => 'Initial stock',
            ]);
        }

        session()->flash('success', 'Inventory item created.');
        return redirect()->route('inventory.show', $item);
    }

    public function render()
    {
        return view('livewire.inventory.create-inventory-component')
            ->layout('components.layouts.app', ['title' => 'Add Inventory Item']);
    }
}
