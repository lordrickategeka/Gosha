<?php

namespace App\Domains\Operations\Models;

use App\Domains\Operations\Enums\WashBayStatus;
use App\Domains\Operations\Enums\WashBayType;
use App\Shared\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WashBay extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'bay_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'status'   => WashBayStatus::class,
        'bay_type' => WashBayType::class,
    ];

    public function washOrders(): HasMany
    {
        return $this->hasMany(WashOrder::class);
    }

    public function currentWashOrder(): HasOne
    {
        return $this->hasOne(WashOrder::class)
            ->where('status', 'in_progress')
            ->latest();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', WashBayStatus::Available);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', WashBayStatus::Occupied);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('bay_type', $type);
    }

    // Helpers
    public function isAvailable(): bool
    {
        return $this->status === WashBayStatus::Available;
    }

    public function isOccupied(): bool
    {
        return $this->status === WashBayStatus::Occupied;
    }

    public function markAsOccupied(): void
    {
        $this->update(['status' => WashBayStatus::Occupied]);
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => WashBayStatus::Available]);
    }

    public function markAsMaintenance(): void
    {
        $this->update(['status' => WashBayStatus::Maintenance]);
    }
}
