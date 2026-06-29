<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VehicleVariant extends Model
{
    protected $fillable = ['vehicle_model_id', 'name', 'year_from', 'year_to', 'engine_code', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function model(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_model_id');
    }

    public function compatibleProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogProduct::class,
            'part_vehicle_compatibilities',
            'vehicle_variant_id',
            'catalog_product_id'
        )->withTimestamps();
    }

    public function fullName(): string
    {
        $m = $this->model;
        return trim(($m?->brand?->name ?? '') . ' ' . ($m?->name ?? '') . ' ' . $this->name);
    }
}
