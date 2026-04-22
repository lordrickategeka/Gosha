<?php

namespace App\Traits;

use App\Models\Vendor;
use App\Scopes\VendorScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToVendor
{
    protected static function bootBelongsToVendor(): void
    {
        static::addGlobalScope(new VendorScope);

        static::creating(function ($model) {
            if (!$model->vendor_id && auth()->check() && auth()->user()->vendor_id) {
                $model->vendor_id = auth()->user()->vendor_id;
            }
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }
}
