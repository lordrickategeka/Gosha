<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    public function vehicleItems(): HasMany
    {
        return $this->hasMany(VehicleItem::class);
    }
    protected $fillable = [
        'customer_id',
        'vehicle_type_id',
        'vehicle_name',
        'number_plate',
        'chasis_number',
        'color',
        'job_card_id',
        'mileage',
        'fuel_type',
        'fuel_level',
        'physical_condition',
        'vin_number'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }

    // Find vehicle by number plate or chassis number
    public static function findByIdentifier($numberPlate = null, $chasisNumber = null)
    {
        $query = static::query();

        if ($numberPlate) {
            $query->orWhere('number_plate', $numberPlate);
        }

        if ($chasisNumber) {
            $query->orWhere('chasis_number', $chasisNumber);
        }

        return $query->first();
    }
}
