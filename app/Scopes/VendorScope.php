<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class VendorScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply if user is authenticated and has a vendor
        if (auth()->check() && auth()->user()->vendor_id && Schema::hasColumn($model->getTable(), 'vendor_id')) {
            $builder->where($model->getTable() . '.vendor_id', auth()->user()->vendor_id);
        }
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutVendorScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('forAllVendors', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
