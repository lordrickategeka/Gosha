<?php

namespace App\Domains\Marketplace\Services;

use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use Illuminate\Validation\ValidationException;

class SupplierPlanAccess
{
    public function assertPlanFits(Vendor $vendor, \App\Domains\Platform\Models\PricingPlan $plan): void
    {
        $listings = \App\Domains\Marketplace\Models\MarketplaceListing::where('supplier_vendor_id', $vendor->id)->where('is_active', true)->count();
        $users = \App\Models\User::where('vendor_id', $vendor->id)->where('is_active', true)->count();
        if (($plan->max_active_listings !== null && $listings > $plan->max_active_listings) || ($plan->max_users !== null && $users > $plan->max_users)) {
            throw ValidationException::withMessages(['plan' => 'This plan is smaller than your current usage. Deactivate listings or remove team members before choosing it.']);
        }
    }

    public function pendingPlan(Vendor $vendor): ?\App\Domains\Platform\Models\PricingPlan
    {
        $invoice = \App\Domains\Platform\Models\VendorPlatformInvoice::where('vendor_id', $vendor->id)->unpaid()->whereNotNull('metadata->target_plan_id')->latest('id')->first();

        return $invoice ? \App\Domains\Platform\Models\PricingPlan::find($invoice->metadata['target_plan_id']) : null;
    }

    public function canSell(?Vendor $vendor): bool
    {
        if (! $vendor?->isVerifiedSupplier() || $vendor->status === 'suspended') {
            return false;
        }
        if ($vendor->vendor_type !== 'supplier') {
            return true; // Existing hybrid garages keep their garage billing rules.
        }
        if ($vendor->status !== 'active') {
            return false;
        }
        $service = app(SelfServiceSubscriptionService::class);
        $subscription = $service->current($vendor);

        return $subscription?->self_service && ! $service->restricted($subscription);
    }

    public function assertSelling(?Vendor $vendor): void
    {
        abort_unless($this->canSell($vendor), 403, 'Supplier approval and an active trial or paid supplier plan are required. Open Billing to continue.');
    }

    public function assertListingCapacity(Vendor $vendor, ?int $exceptId = null): void
    {
        if ($vendor->vendor_type !== 'supplier') {
            return;
        }
        $subscription = app(SelfServiceSubscriptionService::class)->current($vendor);
        $maximum = $subscription?->plan?->max_active_listings;
        $pendingMaximum = $this->pendingPlan($vendor)?->max_active_listings;
        if ($pendingMaximum !== null) {
            $maximum = $maximum === null ? $pendingMaximum : min($maximum, $pendingMaximum);
        }
        if ($maximum === null) {
            return;
        }
        $query = \App\Domains\Marketplace\Models\MarketplaceListing::where('supplier_vendor_id', $vendor->id)->where('is_active', true);
        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }
        if ($query->count() >= $maximum) {
            throw ValidationException::withMessages(['plan' => 'Your active listing limit has been reached. Deactivate a listing or upgrade your supplier plan in Billing.']);
        }
    }
}
