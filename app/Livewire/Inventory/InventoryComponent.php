<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $stockStatus = '';

    protected $queryString = ['search' => ['except' => ''], 'category' => ['except' => '']];

    public function updatingSearch() { $this->resetPage(); }

    public function getCategoriesProperty()
    {
        return InventoryCategory::orderBy('name')->get();
    }

    public function render()
    {
        $items = InventoryItem::with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->stockStatus === 'low', fn($q) => $q->lowStock())
            ->when($this->stockStatus === 'out', fn($q) => $q->outOfStock())
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total' => InventoryItem::count(),
            'low_stock' => InventoryItem::lowStock()->count(),
            'out_of_stock' => InventoryItem::outOfStock()->count(),
            'value' => InventoryItem::selectRaw('SUM(quantity * cost_price) as total')->value('total') ?? 0,
        ];

        return view('livewire.inventory.inventory-component', compact('items', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Inventory']);
    }
}
