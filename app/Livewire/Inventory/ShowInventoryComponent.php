<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Livewire\Component;

class ShowInventoryComponent extends Component
{
    public InventoryItem $item;
    public $showAdjustModal = false;
    public $adjustType = 'adjustment';
    public $adjustQuantity = 0;
    public $adjustNotes = '';

    public function mount(InventoryItem $inventoryItem)
    {
        $this->item = $inventoryItem->load(['category', 'supplier', 'movements' => fn($q) => $q->latest()->take(10)]);
    }

    public function adjustStock()
    {
        $this->validate([
            'adjustType' => 'required|in:purchase,consumption,adjustment,transfer,return',
            'adjustQuantity' => 'required|integer|min:1',
            'adjustNotes' => 'nullable|string|max:500',
        ]);

        $quantity = in_array($this->adjustType, ['consumption', 'transfer']) ? -$this->adjustQuantity : $this->adjustQuantity;

        InventoryMovement::create([
            'inventory_item_id' => $this->item->id,
            'branch_id' => session('current_branch_id'),
            'performed_by' => auth()->id(),
            'movement_type' => $this->adjustType,
            'quantity' => $quantity,
            'unit_cost' => $this->item->cost_price,
            'notes' => $this->adjustNotes,
        ]);

        $this->item->increment('quantity', $quantity);
        $this->item->refresh();

        $this->showAdjustModal = false;
        $this->reset(['adjustQuantity', 'adjustNotes']);
        session()->flash('success', 'Stock adjusted successfully.');
    }

    public function render()
    {
        return view('livewire.inventory.show-inventory-component')
            ->layout('components.layouts.app', ['title' => $this->item->name]);
    }
}
