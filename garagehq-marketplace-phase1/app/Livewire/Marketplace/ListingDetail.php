<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceListing;
use Livewire\Component;

/**
 * STUB — full listing detail (compatibility table, tier breakdown, supplier profile) pending.
 */
class ListingDetail extends Component
{
    public MarketplaceListing $listing;

    public function mount(MarketplaceListing $listing): void
    {
        $this->listing = $listing->load('product.compatibleVariants.model.brand', 'supplier', 'priceTiers');
    }

    public function render()
    {
        return view('livewire.marketplace.listing-detail');
    }
}
