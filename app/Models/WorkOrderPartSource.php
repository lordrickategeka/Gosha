<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderPartSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_item_id',
        'supplier_part_id',
        'supplier_id',
        'currency_id',
        'source_name',
        'source_link',
        'source_part_number',
        'supplier_price',
        'shipping_cost',
        'duty_cost',
        'clearing_cost',
        'margin_amount',
        'margin_percent',
        'total_landed_cost',
        'is_local',
        'is_recommended',
        'confidence_score',
        'reason_codes',
        'availability',
        'lead_time_days',
        'warranty_text',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'supplier_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'duty_cost' => 'decimal:2',
        'clearing_cost' => 'decimal:2',
        'margin_amount' => 'decimal:2',
        'margin_percent' => 'decimal:2',
        'total_landed_cost' => 'decimal:2',
        'is_local' => 'boolean',
        'is_recommended' => 'boolean',
        'confidence_score' => 'integer',
        'reason_codes' => 'array',
        'lead_time_days' => 'integer',
    ];

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function supplierPart(): BelongsTo
    {
        return $this->belongsTo(SupplierPart::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function installationHistory(): HasMany
    {
        return $this->hasMany(PartInstallationHistory::class);
    }
}
