<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartOemNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'oem_part_number',
        'brand_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function outgoingInterchanges(): HasMany
    {
        return $this->hasMany(PartInterchange::class, 'from_part_oem_number_id');
    }

    public function incomingInterchanges(): HasMany
    {
        return $this->hasMany(PartInterchange::class, 'to_part_oem_number_id');
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
