<?php

namespace App\Traits;

use App\Models\Vendor;
use App\Scopes\VendorScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait BelongsToVendor
{
    protected static function bootBelongsToVendor(): void
    {
        static::addGlobalScope(new VendorScope);

        static::creating(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'vendor_id') && !$model->vendor_id && auth()->check() && auth()->user()->vendor_id) {
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
        if (Schema::hasColumn($query->getModel()->getTable(), 'vendor_id')) {
            return $query->where($query->getModel()->getTable() . '.vendor_id', $vendorId);
        }

        return $query;
    }
}
