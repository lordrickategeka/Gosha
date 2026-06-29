<?php

namespace App\Domains\Inventory\Livewire\Inventory;

use App\Shared\Helpers\NumberGeneratorHelper;
use App\Domains\Organization\Models\Branch;
use App\Domains\Inventory\Models\InventoryCategory;
use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Inventory\Models\Supplier;
use Illuminate\Support\Facades\Gate;
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

    public bool $showCreateModal = false;
    public string $createName = '';
    public string $createSku = '';
    public string $createBarcode = '';
    public string $createDescription = '';
    public string $createItemType = 'service_part';
    public string $createCategoryId = '';
    public string $createSupplierId = '';
    public string $createBrand = '';
    public string $createUnit = 'pcs';
    public string $createCondition = 'new';
    public string $createLocation = '';
    public float $createQuantity = 0;
    public float $createCostPrice = 0;
    public float $createSellingPrice = 0;
    public float $createReorderLevel = 5;

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

    public function openCreateModal(): void
    {
        abort_unless(Gate::allows('create_inventory'), 403);

        $this->showCreateModal = true;
        $this->resetCreateForm();
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function updatedCreateItemType(): void
    {
        $this->createCategoryId = '';
        $this->createSku = '';
    }

    public function updatedCreateCategoryId(): void
    {
        $this->syncCreateSkuPreview();
    }

    public function getCreateCategoriesProperty()
    {
        $type = $this->createItemType === 'service_part' ? 'service_parts' : 'wash_supplies';

        return InventoryCategory::where('type', $type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getSuppliersProperty()
    {
        return Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    protected function syncCreateSkuPreview(): void
    {
        if (! $this->createCategoryId) {
            $this->createSku = '';
            return;
        }

        $category = InventoryCategory::find($this->createCategoryId);
        if (! $category) {
            $this->createSku = '';
            return;
        }

        $branchId = (int) session('current_branch_id');
        $branchVendorId = Branch::where('id', $branchId)->value('vendor_id');
        $vendorId = $branchVendorId ?: auth()->user()->vendor_id;
        if (! $vendorId) {
            $this->createSku = '';
            return;
        }

        $categoryCode = $category->code ?: NumberGeneratorHelper::generateInventoryCategoryCode(
            $category->name,
            $category->type,
            $vendorId,
            $category->id,
        );

        $this->createSku = NumberGeneratorHelper::generateInventorySku($categoryCode, $vendorId);
    }

    protected function createRules(): array
    {
        return [
            'createName' => 'required|string|max:255',
            'createItemType' => 'required|in:service_part,wash_supply',
            'createBarcode' => 'nullable|string|max:100',
            'createCategoryId' => 'required|exists:inventory_categories,id',
            'createSupplierId' => 'nullable|exists:suppliers,id',
            'createBrand' => 'nullable|string|max:100',
            'createUnit' => 'required|string|max:20',
            'createCondition' => 'required|in:new,used,refurbished,reconditioned',
            'createQuantity' => 'required|numeric|min:0',
            'createCostPrice' => 'required|numeric|min:0',
            'createSellingPrice' => 'required|numeric|min:0',
            'createReorderLevel' => 'required|numeric|min:0',
            'createLocation' => 'nullable|string|max:100',
            'createDescription' => 'nullable|string|max:1000',
        ];
    }

    public function saveInventoryItem(): void
    {
        abort_unless(Gate::allows('create_inventory'), 403);

        $validated = $this->validate($this->createRules());

        $branchId = (int) session('current_branch_id');
        if (! $branchId) {
            session()->flash('error', 'Please select a branch before creating inventory items.');
            return;
        }

        $branchVendorId = Branch::where('id', $branchId)->value('vendor_id');
        $vendorId = $branchVendorId ?: auth()->user()->vendor_id;

        if (! $vendorId) {
            session()->flash('error', 'Unable to determine vendor for the selected branch.');
            return;
        }

        $item = InventoryItem::create([
            'vendor_id' => $vendorId,
            'branch_id' => $branchId,
            'category_id' => $validated['createCategoryId'],
            'supplier_id' => $validated['createSupplierId'] ?: null,
            'item_type' => $validated['createItemType'],
            'name' => $validated['createName'],
            'brand' => $validated['createBrand'] ?: null,
            'sku' => null,
            'barcode' => $validated['createBarcode'] ?: null,
            'condition' => $validated['createCondition'],
            'unit' => $validated['createUnit'],
            'unit_of_measure' => $validated['createUnit'],
            'quantity' => $validated['createQuantity'],
            'cost_price' => $validated['createCostPrice'],
            'selling_price' => $validated['createSellingPrice'],
            'reorder_level' => $validated['createReorderLevel'],
            'location' => $validated['createLocation'] ?: null,
            'description' => $validated['createDescription'] ?: null,
            'is_active' => true,
        ]);

        if ((float) $validated['createQuantity'] > 0) {
            $item->movements()->create([
                'branch_id' => $branchId,
                'movement_type' => 'purchase',
                'quantity_change' => $validated['createQuantity'],
                'quantity_after' => $validated['createQuantity'],
                'unit_cost' => $validated['createCostPrice'],
                'total_cost' => $validated['createQuantity'] * $validated['createCostPrice'],
                'supplier_id' => $validated['createSupplierId'] ?: null,
                'performed_by' => auth()->id(),
                'notes' => 'Initial stock',
                'movement_date' => now(),
            ]);
        }

        $this->closeCreateModal();
        session()->flash('success', 'Inventory item created successfully.');
    }

    protected function resetCreateForm(): void
    {
        $this->reset([
            'createName',
            'createSku',
            'createBarcode',
            'createDescription',
            'createCategoryId',
            'createSupplierId',
            'createBrand',
            'createLocation',
        ]);

        $this->createItemType = 'service_part';
        $this->createUnit = 'pcs';
        $this->createCondition = 'new';
        $this->createQuantity = 0;
        $this->createCostPrice = 0;
        $this->createSellingPrice = 0;
        $this->createReorderLevel = 5;
        $this->resetValidation();
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
        $branchId = (int) session('current_branch_id');
        $branchVendorId = Branch::where('id', $branchId)->value('vendor_id');
        $vendorId = $branchVendorId ?: auth()->user()->vendor_id;
        $canViewCrossBranchInventory = auth()->check()
            ? Gate::forUser(auth()->user())->allows('view_cross_branch_inventory')
            : false;

        // Default: current branch only. With permission: any branch in same vendor.
        $branchScope = function ($q) use ($branchId, $vendorId, $canViewCrossBranchInventory) {
            if ($canViewCrossBranchInventory && $vendorId) {
                $q->where('vendor_id', $vendorId)
                    ->whereNotNull('branch_id');
                return;
            }

            $q->where('branch_id', $branchId);
        };

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
