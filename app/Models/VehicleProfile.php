<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'vin',
        'chassis_code',
        'make',
        'model',
        'trim',
        'year',
        'engine_code',
        'transmission',
        'drivetrain',
        'market_region',
        'decoded_source',
        'decoded_at',
    ];

    protected $casts = [
        'decoded_at' => 'datetime',
        'year' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
