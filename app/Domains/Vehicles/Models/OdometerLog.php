<?php

namespace App\Domains\Vehicles\Models;

use App\Enums\OdometerSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdometerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'reading_date',
        'odometer_value',
        'engine_hours',
        'source',
    ];

    protected $casts = [
        'reading_date' => 'datetime',
        'odometer_value' => 'integer',
        'engine_hours' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getSourceLabelAttribute(): string
    {
        return OdometerSource::from($this->source)->label() ?? $this->source;
    }
}
