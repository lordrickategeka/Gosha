<?php

namespace App\Domains\Marketplace\Livewire\Buyer;

use App\Domains\Marketplace\Models\MarketplaceListing;
use Livewire\Component;

class ListingDetail extends Component
{
    public MarketplaceListing $listing;

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

    public function mount(MarketplaceListing $listing)
    {
        // Redirect to vendor registration if no vendor found and not a platform user
        if (!$this->hasVendor()) {
            return $this->redirect(route('register'), navigate: true);
        }

        $this->listing = $listing;
    }

    public function render()
    {
        return view('livewire.marketplace.buyer.listing-detail');
    }
}
