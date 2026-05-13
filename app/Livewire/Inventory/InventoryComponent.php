<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $itemType = ''; // service_part, wash_supply
    public string $stockStatus = ''; // low, out, in_stock
    public string $condition = ''; // new, used, refurbished
    public bool $showExpired = false;
    public bool $showExpiringSoon = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'itemType' => ['except' => ''],
        'stockStatus' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingItemType()
    {
        $this->resetPage();
        $this->category = ''; // Reset category when type changes
    }

    public function updatingStockStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'category', 'itemType', 'stockStatus', 'condition', 'showExpired', 'showExpiringSoon']);
        $this->dispatch('filtersCleared');
    }

    public function getCategoriesProperty()
    {
        $query = InventoryCategory::orderBy('name');

        // Filter categories by item type if selected
        if ($this->itemType) {
            $query->where('type', $this->itemType === 'service_part' ? 'service_parts' : 'wash_supplies');
        }

        return $query->get();
    }

    public function render()
    {
        $branchId = session('current_branch_id');

        // Branch scope: current branch + vendor-level items (branch_id null)
        $branchScope = fn($q) => $q->where(function ($s) use ($branchId) {
            $s->where('branch_id', $branchId)->orWhereNull('branch_id');
        });

        $items = InventoryItem::with(['category', 'supplier', 'branch'])
            ->tap($branchScope)
            ->when($this->search, fn($q) => $q->where(function ($s) {
                $s->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('barcode', 'like', "%{$this->search}%")
                    ->orWhere('oem_number', 'like', "%{$this->search}%")
                    ->orWhere('aftermarket_number', 'like', "%{$this->search}%");
            }))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->itemType, fn($q) => $q->where('item_type', $this->itemType))
            ->when($this->condition, fn($q) => $q->where('condition', $this->condition))
            ->when($this->stockStatus === 'low', fn($q) => $q->lowStock())
            ->when($this->stockStatus === 'out', fn($q) => $q->outOfStock())
            ->when($this->stockStatus === 'in_stock', fn($q) => $q->inStock())
            ->when($this->showExpired, fn($q) => $q->expired())
            ->when($this->showExpiringSoon, fn($q) => $q->expiringSoon(30))
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(50); // Increased to show more items per page

        // Group items by category for display
        $groupedItems = $items->getCollection()->groupBy(function ($item) {
            return $item->category?->full_path ?? 'Uncategorized';
        });

        $stats = [
            'total' => InventoryItem::tap($branchScope)->where('is_active', true)->count(),
            'service_parts' => InventoryItem::tap($branchScope)->serviceParts()->count(),
            'wash_supplies' => InventoryItem::tap($branchScope)->washSupplies()->count(),
            'low_stock' => InventoryItem::tap($branchScope)->lowStock()->count(),
            'out_of_stock' => InventoryItem::tap($branchScope)->outOfStock()->count(),
            'total_value' => InventoryItem::tap($branchScope)
                ->selectRaw('SUM(quantity * cost_price) as total')
                ->value('total') ?? 0,
            'expiring_soon' => InventoryItem::tap($branchScope)->expiringSoon(30)->count(),
            'expired' => InventoryItem::tap($branchScope)->expired()->count(),
        ];

        return view('livewire.inventory.inventory-component', compact('items', 'groupedItems', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Inventory Management']);
    }
}
