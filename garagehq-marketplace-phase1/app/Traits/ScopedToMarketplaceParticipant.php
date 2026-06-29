<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Marketplace cross-tenant scoping.
 *
 * The rest of GarageHQ is locked to one tenant by the global BelongsToVendor scope
 * (everything filtered by vendor_id). The marketplace deliberately crosses that boundary:
 * a buyer (vendor A) transacts with a supplier (vendor B). Applying the global scope here
 * would silently return EMPTY result sets, so marketplace models do NOT use BelongsToVendor.
 *
 * Instead they carry explicit named keys (buyer_vendor_id / supplier_vendor_id) and use the
 * local scopes below to filter by the *active participant* depending on which side of the
 * marketplace the current request is on.
 *
 * Requirements on the consuming model:
 *   - Define public array $participantColumns, listing the participant FK columns present
 *     on the table, e.g. ['buyer_vendor_id', 'supplier_vendor_id'] or ['supplier_vendor_id'].
 *
 * Current vendor id is resolved from session('current_vendor_id') with a fallback to the
 * authenticated user's vendor_id. (Branch context still rides on session('current_branch_id')
 * where a branch_id column exists, but is applied per-query by callers, not globally.)
 */
trait ScopedToMarketplaceParticipant
{
    public static function currentVendorId(): ?int
    {
        if ($id = session('current_vendor_id')) {
            return (int) $id;
        }

        return optional(auth()->user())->vendor_id;
    }

    /** Rows where the current vendor is the buyer. */
    public function scopeAsBuyer(Builder $query, ?int $vendorId = null): Builder
    {
        $vendorId ??= static::currentVendorId();

        return $query->where('buyer_vendor_id', $vendorId);
    }

    /** Rows where the current vendor is the supplier. */
    public function scopeAsSupplier(Builder $query, ?int $vendorId = null): Builder
    {
        $vendorId ??= static::currentVendorId();

        return $query->where('supplier_vendor_id', $vendorId);
    }

    /**
     * Rows where the current vendor participates on EITHER side. Use this as the safe default
     * for any list a vendor should see "their" rows in (orders, transactions).
     */
    public function scopeForCurrentParticipant(Builder $query, ?int $vendorId = null): Builder
    {
        $vendorId ??= static::currentVendorId();
        $cols = $this->participantColumns ?? ['buyer_vendor_id', 'supplier_vendor_id'];

        return $query->where(function (Builder $q) use ($cols, $vendorId) {
            foreach ($cols as $col) {
                $q->orWhere($col, $vendorId);
            }
        });
    }
}
