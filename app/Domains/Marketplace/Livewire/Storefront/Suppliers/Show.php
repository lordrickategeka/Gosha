<?php

namespace App\Domains\Marketplace\Livewire\Storefront\Suppliers;

use App\Domains\Marketplace\Models\MarketplaceListing;
use App\Domains\Platform\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.storefront')]
#[Title('Supplier — GarageHQ Marketplace')]
class Show extends Component
{
    use WithPagination;

    public Vendor $vendor;

    public function mount(Vendor $vendor): void
    {
        abort_unless(app(\App\Domains\Marketplace\Services\SupplierPlanAccess::class)->canSell($vendor), 404);
        // Only verified suppliers have a public profile — anything else 404s, same trust
        // boundary as the storefront's browsable() listings.
        abort_unless(
            in_array($vendor->vendor_type, ['supplier', 'hybrid'], true) && $vendor->is_verified_supplier,
            404
        );

        $this->vendor = $vendor;
    }

    public function render()
    {
        $listings = MarketplaceListing::browsable()
            ->where('supplier_vendor_id', $this->vendor->id)
            ->with(['product.category', 'priceTiers'])
            ->orderBy('price')
            ->paginate(24);

        return view('livewire.marketplace.storefront.suppliers.show', compact('listings'));
    }
}
