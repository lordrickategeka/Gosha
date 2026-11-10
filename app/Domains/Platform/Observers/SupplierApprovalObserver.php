<?php

namespace App\Domains\Platform\Observers;

use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;

class SupplierApprovalObserver
{
    public function updated(Vendor $vendor): void
    {
        if ($vendor->wasChanged('is_verified_supplier') && $vendor->is_verified_supplier) {
            app(SelfServiceSubscriptionService::class)->approveSupplier($vendor);
        }
    }
}
