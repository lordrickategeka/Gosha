<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Catalog;

use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Platform\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Supplier-facing browse of the shared, platform-owned catalog. Read-only here — proposing
 * a new product happens in Catalog\Create, which is gated to verified suppliers only. This
 * page shows everyone's verified products (reference for what already exists) plus this
 * vendor's own pending submissions so they can track moderation status.
 */
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $mineOnly = false;

    private function vendorId(): ?int
    {
        $vendorId = session('current_vendor_id') ?? auth()->user()?->vendor_id;

        return $vendorId ? (int) $vendorId : null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMineOnly()
    {
        $this->resetPage();
    }

    public function render()
    {
        $vendorId = $this->vendorId();
        $vendor = $vendorId ? Vendor::find($vendorId) : null;

        $products = CatalogProduct::query()
            ->with('category')
            ->where(function ($q) use ($vendorId) {
                $q->where('is_verified', true)
                    ->orWhere('created_by_vendor_id', $vendorId);
            })
            ->when($this->mineOnly, fn ($q) => $q->where('created_by_vendor_id', $vendorId))
            ->when($this->search, fn ($q) => $q->where(function ($p) {
                $p->where('name', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%")
                    ->orWhere('part_number', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(20);

        return view('livewire.marketplace.supplier.catalog.index', [
            'products' => $products,
            'isHybrid' => $vendor?->vendor_type === 'hybrid',
            'isVerifiedSupplier' => app(\App\Domains\Marketplace\Services\SupplierPlanAccess::class)->canSell($vendor),
        ]);
    }
}
