<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Quotes;

use App\Domains\Marketplace\Enums\RfqStatus;
use App\Domains\Marketplace\Models\Rfq;
use Livewire\Component;
use Livewire\WithPagination;

class Inbox extends Component
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
        // Check vendor again in render (covers direct access cases)
        if (!$this->hasVendor()) {
            return $this->redirect(route('register'), navigate: true);
        }

        $vendorId = $this->getVendorId();

        // Open RFQs the supplier can quote on: open visibility OR explicitly invited.
        $rfqs = Rfq::query()
            ->with(['items', 'buyer'])
            ->where('status', RfqStatus::Published)
            ->where(function ($q) use ($vendorId) {
                $q->where('visibility', 'open')
                  ->orWhereHas('invitations', fn ($i) => $i->where('supplier_vendor_id', $vendorId));
            })
            ->withCount(['quotes as has_quoted' => fn ($q) => $q->where('supplier_vendor_id', $vendorId)])
            ->latest()
            ->paginate(15);

        return view('livewire.marketplace.supplier.quotes.inbox', compact('rfqs'));
    }
}
