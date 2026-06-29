<?php

namespace App\Domains\Marketplace\Livewire\Storefront;

use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\MarketplaceListing;
use App\Domains\Marketplace\Models\PartCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Public-facing storefront. No auth required.
 * Uses a standalone layout without navbar/sidebar.
 */
#[Layout('layouts.storefront')]
#[Title('Marketplace')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $category_id = null;

    public string $sort_by = 'price_asc';

    protected function getListings()
    {
        return MarketplaceListing::browsable()
            ->with(['product.category', 'supplier', 'priceTiers'])
            ->when($this->category_id, fn ($q) => $q->whereHas('product', fn ($p) =>
                $p->where('category_id', $this->category_id)))
            ->when($this->search, fn ($q) => $q->whereHas('product', fn ($p) =>
                $p->where('name', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%")
                  ->orWhere('part_number', 'like', "%{$this->search}%")))
            ->when($this->sort_by === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($this->sort_by === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->when($this->sort_by === 'newest', fn ($q) => $q->orderByDesc('created_at'))
            ->when($this->sort_by === 'name_asc', fn ($q) => $q->orderBy('product.name'))
            ->orderByDesc(
                \App\Domains\Platform\Models\Vendor::select('is_verified_supplier')
                    ->whereColumn('vendors.id', 'marketplace_listings.supplier_vendor_id')
            )
            ->paginate(24);
    }

    public function render()
    {
        $listings = $this->getListings();

        $categories = PartCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return view('livewire.marketplace.storefront.index', compact('listings', 'categories'));
    }
}
