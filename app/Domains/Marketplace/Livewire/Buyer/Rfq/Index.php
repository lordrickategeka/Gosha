<?php

namespace App\Domains\Marketplace\Livewire\Buyer\Rfq;

use App\Domains\Marketplace\Models\Rfq;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lean listing of the buyer's own RFQs (asBuyer scope).
 */
class Index extends Component
{
    use WithPagination;

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

    public function render()
    {
        $rfqs = Rfq::asBuyer()
            ->withCount('quotes')
            ->latest()
            ->paginate(15);

        return view('livewire.marketplace.buyer.rfq.index', compact('rfqs'));
    }
}
