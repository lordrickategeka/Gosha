<?php

namespace App\Domains\Vehicles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtcLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'code',
        'description',
        'logged_at',
        'cleared_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'cleared_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('cleared_at');
    }

    public function scopeCleared($query)
    {
        return $query->whereNotNull('cleared_at');
    }

    public function isCleared(): bool
    {
        return $this->cleared_at !== null;
    }

    public function getCategoryAttribute(): string
    {
        return match (substr($this->code, 0, 1)) {
            'P' => 'Powertrain',
            'B' => 'Body',
            'C' => 'Chassis',
            'U' => 'Network',
            default => 'Unknown',
        };
    }
}
