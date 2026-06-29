<?php

namespace App\Domains\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartFitmentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'part_oem_number_id',
        'make',
        'model',
        'year_from',
        'year_to',
        'chassis_code',
        'engine_code',
        'transmission',
        'drivetrain',
        'market_region',
        'fitment_match_weight',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'year_from' => 'integer',
        'year_to' => 'integer',
        'fitment_match_weight' => 'integer',
        'is_active' => 'boolean',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function oemNumber(): BelongsTo
    {
        return $this->belongsTo(PartOemNumber::class, 'part_oem_number_id');
    }
}
