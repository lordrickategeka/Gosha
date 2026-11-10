<?php

namespace App\Domains\Platform\Observers;

use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;

class SupplierApprovalObserver
{
    public function updated(Vendor $vendor): void
    {
        $verificationChanged = $vendor->wasChanged('is_verified_supplier');
        if ($verificationChanged && $vendor->supplier_requested_at && ! $vendor->wasChanged('supplier_requested_at')) {
            $vendor->forceFill(['supplier_reviewed_at' => now()])->saveQuietly();
            $owners = $vendor->users()->where('is_active', true)->where('is_platform_user', false)->role('vendor-owner')->get();
            \Illuminate\Support\Facades\Notification::send($owners, new \App\Domains\Marketplace\Notifications\SupplierModuleNotice($vendor->id, $vendor->name, $vendor->is_verified_supplier ? 'approved' : 'paused'));
        }
        if ($verificationChanged && $vendor->is_verified_supplier) {
            app(SelfServiceSubscriptionService::class)->approveSupplier($vendor);
        }
    }
}
