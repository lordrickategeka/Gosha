<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'category',
        'position',
        'oem_group_key',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function oemNumbers(): HasMany
    {
        return $this->hasMany(PartOemNumber::class);
    }

    public function fitmentRules(): HasMany
    {
        return $this->hasMany(PartFitmentRule::class);
    }

    public function supplierParts(): HasMany
    {
        return $this->hasMany(SupplierPart::class);
    }

    public function installationHistory(): HasMany
    {
        return $this->hasMany(PartInstallationHistory::class);
    }
}
