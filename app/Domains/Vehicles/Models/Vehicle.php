<?php

namespace App\Domains\Vehicles\Models;

use App\Domains\CRM\Models\Customer;
use App\Domains\Operations\Models\Appointment;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use App\Enums\VehicleStatus;
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
        'vendor_id',
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
        'engine_code',
        'engine_displacement',
        'drivetrain_type',
        'transmission_code',
        'transmission_type',
        'in_service_date',
        'acquisition_date',
        'acquisition_cost',
        'ownership_status',
        'lease_end_date',
        'lease_mileage_limit',
        'current_value',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'engine_displacement' => 'decimal:1',
        'acquisition_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'in_service_date' => 'date',
        'acquisition_date' => 'date',
        'lease_end_date' => 'date',
        'lease_mileage_limit' => 'integer',
    ];

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

    public function odometerLogs(): HasMany
    {
        return $this->hasMany(OdometerLog::class);
    }

    public function warrantyPolicies(): HasMany
    {
        return $this->hasMany(WarrantyPolicy::class);
    }

    public function complianceRecords(): HasMany
    {
        return $this->hasMany(ComplianceRecord::class);
    }

    public function dtcLogs(): HasMany
    {
        return $this->hasMany(DtcLog::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('registration_number', 'like', "%{$search}%")
              ->orWhere('make', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

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

    public function getStatusLabelAttribute(): string
    {
        return VehicleStatus::from($this->status)->label() ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        return VehicleStatus::from($this->status)->color() ?? 'gray';
    }

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

    public function latestOdometerReading(): ?OdometerLog
    {
        return $this->odometerLogs()
            ->orderByDesc('reading_date')
            ->first();
    }

    public function averageAnnualMileage(): ?float
    {
        if (!$this->in_service_date || !$this->mileage) {
            return null;
        }

        $years = $this->in_service_date->diffInYears(now());

        if ($years < 1) {
            return (float) $this->mileage;
        }

        return round($this->mileage / $years);
    }

    public function getActiveWarranties()
    {
        return $this->warrantyPolicies()
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->get();
    }

    public function isOverdueForService(int $serviceIntervalMiles = 5000): bool
    {
        if (!$this->mileage) {
            return false;
        }

        $lastService = $this->lastService();

        if (!$lastService) {
            if ($this->in_service_date) {
                $monthsSinceService = $this->in_service_date->diffInMonths(now());
                return $monthsSinceService >= 6;
            }
            return false;
        }

        $milesSinceService = $this->mileage - ($lastService->mileage ?? 0);

        return $milesSinceService >= $serviceIntervalMiles;
    }

    public function getExpiringCompliance(int $days = 30)
    {
        return $this->complianceRecords()
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now())
            ->get();
    }

    public function getActiveDTCs()
    {
        return $this->dtcLogs()
            ->whereNull('cleared_at')
            ->get();
    }

    public function calculateDepreciation(): ?float
    {
        if (!$this->acquisition_cost || !$this->current_value) {
            return null;
        }

        return $this->acquisition_cost - $this->current_value;
    }

    public function depreciationPercentage(): ?float
    {
        if (!$this->acquisition_cost || $this->acquisition_cost == 0) {
            return null;
        }

        $depreciation = $this->calculateDepreciation();

        if ($depreciation === null) {
            return null;
        }

        return round(($depreciation / $this->acquisition_cost) * 100, 2);
    }
}
