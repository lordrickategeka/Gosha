<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'registration_number',
        'make',
        'model',
        'year',
        'color',
        'vin',
        'chassis_number',
        'engine_number',
        'fuel_type',
        'transmission',
        'mileage',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function washOrders(): HasMany
    {
        return $this->hasMany(WashOrder::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(VehicleProfile::class);
    }

    // Scopes
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('registration_number', 'like', "%{$search}%")
              ->orWhere('make', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([
            $this->registration_number,
            $this->make,
            $this->model,
            $this->year,
        ]);
        return implode(' - ', $parts);
    }

    public function getFullDescriptionAttribute(): string
    {
        $parts = array_filter([
            $this->year,
            $this->make,
            $this->model,
            $this->color ? "({$this->color})" : null,
        ]);
        return implode(' ', $parts);
    }

    // Helpers
    public function lastService()
    {
        return $this->workOrders()
            ->where('status', 'delivered')
            ->latest('completed_at')
            ->first();
    }

    public function lastWash()
    {
        return $this->washOrders()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();
    }

    public function serviceHistory()
    {
        return $this->workOrders()
            ->where('status', 'delivered')
            ->orderByDesc('completed_at')
            ->get();
    }

    public function updateMileage(int $mileage): void
    {
        if ($mileage > ($this->mileage ?? 0)) {
            $this->update(['mileage' => $mileage]);
        }
    }
}
