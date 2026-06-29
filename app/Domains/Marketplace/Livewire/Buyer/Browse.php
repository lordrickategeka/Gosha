<?php

namespace App\Domains\Marketplace\Livewire\Buyer;

use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\MarketplaceListing;
use App\Domains\Marketplace\Services\PurchaseOrderService;
use App\Domains\Platform\Models\Vendor;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buyer-facing marketplace browse. Uses MarketplaceListing::browsable() which deliberately
 * IGNORES vendor ownership — this is the cross-tenant view. The global vendor scope is NOT
 * present on this model, so listings from every supplier are visible.
 *
 * Reorder hook: arriving with ?catalog_product_id=NN (e.g. from a low-stock notification's
 * "Reorder from marketplace" action) pre-filters to listings for that product.
 */
class Browse extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $catalog_product_id = null;

    #[Url]
    public ?int $category_id = null;

    private function getVendorId(): ?int
    {
        $vendorId = session('current_vendor_id') ?? auth()->user()?->vendor_id;

        return $vendorId ? (int) $vendorId : null;
    }

    private function hasVendor(): bool
    {
        // Platform users (super admins) can view without a vendor
        if (auth()->user()?->is_platform_user) {
            return true;
        }

        return $this->getVendorId() !== null;
    }

    public function mount()
    {
        // Redirect to vendor registration if no vendor found and not a platform user
        if (!$this->hasVendor()) {
            return $this->redirect(route('register'), navigate: true);
        }
    }

    /** Direct purchase from a listing -> draft PO for the buyer. */
    public function buy(int $listingId, int $qty = 1): void
    {
        $this->authorize('place_marketplace_orders');

        $listing = MarketplaceListing::browsable()->findOrFail($listingId);
        $qty = max($qty, $listing->min_order_qty);

        $po = app(PurchaseOrderService::class)->createFromListing(
            listing: $listing,
            qty: $qty,
            buyerVendorId: $this->getVendorId(),
            branchId: session('current_branch_id'),
        );

        $this->dispatch('toast', message: "Draft PO {$po->po_number} created.");
        $this->redirectRoute('marketplace.purchase-orders.index', navigate: true);
    }

    public function render()
    {
        $listings = MarketplaceListing::browsable()
            ->with(['product.category', 'supplier', 'priceTiers'])
            ->when($this->catalog_product_id, fn ($q) => $q->where('catalog_product_id', $this->catalog_product_id))
            ->when($this->category_id, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('category_id', $this->category_id)))
            ->when($this->search, fn ($q) => $q->whereHas('product', fn ($p) =>
                $p->where('name', 'like', "%{$this->search}%")
                  ->orWhere('brand', 'like', "%{$this->search}%")
                  ->orWhere('part_number', 'like', "%{$this->search}%")))
            // Cheapest first; verified-supplier listings surface higher.
            ->orderByDesc(
                Vendor::select('is_verified_supplier')
                    ->whereColumn('vendors.id', 'marketplace_listings.supplier_vendor_id')
            )
            ->orderBy('price')
            ->paginate(18);

        $focusProduct = $this->catalog_product_id
            ? CatalogProduct::find($this->catalog_product_id)
            : null;

        return view('livewire.marketplace.buyer.browse', compact('listings', 'focusProduct'));
    }
}
