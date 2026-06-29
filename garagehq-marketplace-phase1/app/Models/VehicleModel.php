<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    protected $fillable = ['vehicle_brand_id', 'name', 'slug', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(VehicleVariant::class);
    }
}
