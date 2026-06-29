<?php

namespace App\Domains\Operations\Models;

use App\Shared\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceBay extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'bay_type',
        'status',
        'notes',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function currentWorkOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class)
            ->whereIn('status', ['open', 'in_progress', 'quality_check'])
            ->latest();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('bay_type', $type);
    }

    // Helpers
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function markAsOccupied(): void
    {
        $this->update(['status' => 'occupied']);
    }

    public function markAsAvailable(): void
    {
        $this->update(['status' => 'available']);
    }

    public function markAsMaintenance(): void
    {
        $this->update(['status' => 'maintenance']);
    }
}
