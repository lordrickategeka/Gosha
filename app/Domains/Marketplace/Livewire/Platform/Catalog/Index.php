<?php

namespace App\Domains\Marketplace\Livewire\Platform\Catalog;

use App\Domains\Marketplace\Models\CatalogProduct;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Platform-admin catalog moderation. "Pending" is the queue of supplier-proposed products
 * awaiting the is_verified flip that lets them appear on the public storefront (see
 * MarketplaceListing::scopeBrowsable()); "All" is a general management view.
 */
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tab = 'pending';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTab()
    {
        $this->resetPage();
    }

    public function verify(int $id): void
    {
        $this->authorize('verify_catalog_products');

        CatalogProduct::whereKey($id)->update(['is_verified' => true]);

        $this->dispatch('toast', message: 'Product verified — now visible on the public storefront.');
    }

    /** Rejecting deactivates rather than deletes, so the proposer's submission history stays intact. */
    public function reject(int $id): void
    {
        $this->authorize('verify_catalog_products');

        CatalogProduct::whereKey($id)->update(['is_verified' => false, 'is_active' => false]);

        $this->dispatch('toast', message: 'Product rejected and deactivated.');
    }

    public function render()
    {
        $products = CatalogProduct::query()
            ->with(['category', 'proposer', 'listings'])
            ->withCount('listings')
            ->when($this->tab === 'pending', fn ($q) => $q->where('is_verified', false)->where('is_active', true))
            ->when($this->tab === 'verified', fn ($q) => $q->where('is_verified', true))
            ->when($this->tab === 'rejected', fn ($q) => $q->where('is_verified', false)->where('is_active', false))
            ->when($this->search, fn ($q) => $q->where(function ($p) {
                $p->where('name', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%")
                    ->orWhere('part_number', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(20);

        $counts = [
            'pending' => CatalogProduct::where('is_verified', false)->where('is_active', true)->count(),
            'verified' => CatalogProduct::where('is_verified', true)->count(),
            'rejected' => CatalogProduct::where('is_verified', false)->where('is_active', false)->count(),
        ];

        return view('livewire.marketplace.platform.catalog.index', compact('products', 'counts'));
    }
}
