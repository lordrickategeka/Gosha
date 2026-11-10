<?php

namespace App\Domains\Marketplace\Services;

use App\Domains\Marketplace\Notifications\SupplierModuleNotice;
use App\Domains\Platform\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupplierModuleService
{
    public function canRequest(User $user): bool
    {
        return $user->is_active && ! $user->is_platform_user && $user->hasRole('vendor-owner')
            && in_array($user->vendor?->vendor_type, ['garage', 'car_wash', 'hybrid'], true);
    }

    public function request(User $user, string $notes): Vendor
    {
        abort_unless($this->canRequest($user), 403, 'Only the business owner can add the supplier module.');

        return DB::transaction(function () use ($user, $notes) {
            // Never use a browser-supplied vendor ID or the platform context switcher.
            $vendor = Vendor::whereKey($user->vendor_id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($vendor->status, ['active', 'trial'], true), 403, 'Your business must be active before adding supplier access.');
            abort_unless(in_array($vendor->vendor_type, ['garage', 'car_wash', 'hybrid'], true), 403);

            if (! $vendor->supplier_requested_at) {
                $alreadyApproved = $vendor->vendor_type === 'hybrid' && $vendor->is_verified_supplier;
                $vendor->update(['is_verified_supplier' => $alreadyApproved,
                    'vendor_type' => 'hybrid', 'supplier_requested_at' => now(), 'supplier_reviewed_at' => $alreadyApproved ? now() : null, 'supplier_application_notes' => $notes]);
                if (! $alreadyApproved) {
                    $reviewers = User::where('is_platform_user', true)->where('is_active', true)->permission('verify_catalog_products')->get();
                    \Illuminate\Support\Facades\Notification::send($reviewers, new SupplierModuleNotice($vendor->id, $vendor->name, 'requested'));
                }
            }
            // Add supplier capabilities to this owner; retain all garage roles and staff.
            $user->assignRole('supplier_admin');

            return $vendor;
        });
    }
}
