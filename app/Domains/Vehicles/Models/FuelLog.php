<?php

namespace App\Domains\Vehicles\Models;

use App\Enums\OdometerSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'date',
        'liters',
        'cost',
        'odometer_reading',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'liters' => 'decimal:2',
        'cost' => 'decimal:2',
        'odometer_reading' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getSourceLabelAttribute(): string
    {
        return OdometerSource::from($this->source)->label() ?? $this->source;
    }

    public function getPricePerLiterAttribute(): ?float
    {
        if ($this->liters > 0) {
            return $this->cost / $this->liters;
        }
        return null;
    }
}
