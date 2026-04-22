<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Livewire\Component;
use Livewire\WithPagination;

class Movements extends Component
{
    use WithPagination;

    public $item = '';
    public $type = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['item' => ['except' => ''], 'type' => ['except' => '']];

    public function getItemsProperty()
    {
        return InventoryItem::orderBy('name')->get();
    }

    public function render()
    {
        $movements = InventoryMovement::with(['inventoryItem', 'performedBy'])
            ->when($this->item, fn($q) => $q->where('inventory_item_id', $this->item))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(25);

        return view('livewire.inventory.movements', compact('movements'))
            ->layout('components.layouts.app', ['title' => 'Stock Movements']);
    }
}
