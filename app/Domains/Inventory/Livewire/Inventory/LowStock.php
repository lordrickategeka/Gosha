<?php

namespace App\Domains\Inventory\Livewire\Inventory;

use Livewire\Component;
use App\Domains\Inventory\Models\InventoryItem;
use Livewire\WithPagination;

class LowStock extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $items = InventoryItem::with('category', 'supplier')
            ->where(function ($query) {
                $query->whereColumn('quantity', '<=', 'reorder_level')
                      ->orWhere('quantity', '<=', 0);
            })
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByRaw('CASE WHEN quantity <= 0 THEN 0 ELSE 1 END')
            ->orderBy('quantity')
            ->paginate(20);

        $stats = [
            'out_of_stock' => InventoryItem::outOfStock()->count(),
            'low_stock' => InventoryItem::lowStock()->count(),
            'total_value_at_risk' => InventoryItem::lowStock()->selectRaw('SUM(reorder_level * cost_price) as total')->value('total') ?? 0,
        ];

        return view('livewire.inventory.low-stock', compact('items', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Low Stock Alert']);
    }
}
