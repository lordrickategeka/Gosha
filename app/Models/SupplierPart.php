<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'part_id',
        'part_oem_number_id',
        'currency_id',
        'supplier_part_name',
        'supplier_part_number',
        'supplier_link',
        'supplier_price',
        'availability',
        'lead_time_days',
        'warranty_text',
        'shipping_cost',
        'duty_cost',
        'clearing_cost',
        'margin_amount',
        'margin_percent',
        'is_local',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'supplier_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'duty_cost' => 'decimal:2',
        'clearing_cost' => 'decimal:2',
        'margin_amount' => 'decimal:2',
        'margin_percent' => 'decimal:2',
        'is_local' => 'boolean',
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function oemNumber(): BelongsTo
    {
        return $this->belongsTo(PartOemNumber::class, 'part_oem_number_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function workOrderPartSources(): HasMany
    {
        return $this->hasMany(WorkOrderPartSource::class);
    }

    public function installationHistory(): HasMany
    {
        return $this->hasMany(PartInstallationHistory::class);
    }
}
