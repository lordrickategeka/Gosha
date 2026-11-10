<?php

namespace App\Domains\Platform\Services;

use App\Domains\Organization\Models\Branch;
use App\Domains\Platform\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SubscriptionCapacityGuard
{
    public function creating(Model $model, string $limit, bool $monthly = false): void
    {
        $vendorId = $model->vendor_id ?: ($model->branch_id ? Branch::find($model->branch_id)?->vendor_id : null);
        if (! $vendorId) {
            return;
        }
        $subscription = app(SelfServiceSubscriptionService::class)->current(Vendor::find($vendorId));
        if (! $subscription?->self_service) {
            return;
        }
        $maximum = $subscription->plan->$limit;
        if ($model instanceof \App\Models\User && $subscription->vendor->vendor_type === 'supplier') {
            $pending = app(\App\Domains\Marketplace\Services\SupplierPlanAccess::class)->pendingPlan($subscription->vendor)?->max_users;
            if ($pending !== null) {
                $maximum = $maximum === null ? $pending : min($maximum, $pending);
            }
        }
        if ($maximum === null) {
            return;
        }
        $query = $model->newQueryWithoutScopes();
        if ($model instanceof \App\Models\User) {
            $query->where('is_active', true);
        }
        if (Schema::hasColumn($model->getTable(), 'vendor_id')) {
            $query->where('vendor_id', $vendorId);
        } else {
            $query->whereIn('branch_id', Branch::where('vendor_id', $vendorId)->pluck('id'));
        }
        if (Schema::hasColumn($model->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
        if ($monthly) {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }
        if ($query->count() >= $maximum) {
            throw ValidationException::withMessages(['plan' => 'Your plan limit has been reached. Choose a larger plan from Billing.']);
        }
    }
}
