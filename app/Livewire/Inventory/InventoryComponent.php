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
        $branchId = session('current_branch_id');

        $branchScope = fn($q) => $q->where(function ($s) use ($branchId) {
            $s->where('branch_id', $branchId)->orWhereNull('branch_id');
        });

        $items = InventoryItem::with('category')
            ->tap($branchScope)
            ->when($this->search, fn($q) => $q->where(function ($s) {
                $s->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            }))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->stockStatus === 'low', fn($q) => $q->lowStock())
            ->when($this->stockStatus === 'out', fn($q) => $q->outOfStock())
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total'       => InventoryItem::tap($branchScope)->count(),
            'low_stock'   => InventoryItem::tap($branchScope)->lowStock()->count(),
            'out_of_stock'=> InventoryItem::tap($branchScope)->outOfStock()->count(),
            'value'       => InventoryItem::tap($branchScope)->selectRaw('SUM(quantity * cost_price) as total')->value('total') ?? 0,
        ];

        return view('livewire.inventory.inventory-component', compact('items', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Inventory']);
    }
}
