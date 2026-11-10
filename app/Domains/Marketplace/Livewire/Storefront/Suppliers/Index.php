<?php

namespace App\Domains\Marketplace\Livewire\Storefront\Suppliers;

use App\Domains\Platform\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Public supplier directory. Only verified suppliers/hybrids are listed — this is the
 * public trust signal, mirroring the browsable()/is_verified gate on listings.
 */
#[Layout('layouts.storefront')]
#[Title('Suppliers — GarageHQ Marketplace')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $suppliers = Vendor::query()
            ->whereIn('vendor_type', ['supplier', 'hybrid'])
            ->sellingAccess()
            ->withCount(['marketplaceListings' => fn ($q) => $q->browsable()])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('supplier_rating_avg')
            ->paginate(24);

        return view('livewire.marketplace.storefront.suppliers.index', compact('suppliers'));
    }
}
