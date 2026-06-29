<?php

namespace App\Domains\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartInterchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_part_oem_number_id',
        'to_part_oem_number_id',
        'interchange_type',
        'year_from',
        'year_to',
        'market_region',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'year_from' => 'integer',
        'year_to' => 'integer',
    ];

    public function fromOemNumber(): BelongsTo
    {
        return $this->belongsTo(PartOemNumber::class, 'from_part_oem_number_id');
    }

    public function toOemNumber(): BelongsTo
    {
        return $this->belongsTo(PartOemNumber::class, 'to_part_oem_number_id');
    }
}
