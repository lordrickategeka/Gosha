<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\GeneratesOrderNumber;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WashOrder extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, HasAuditLog, GeneratesOrderNumber;

    protected $orderNumberPrefix = 'WSH';

    protected $fillable = [
        'branch_id',
        'vehicle_id',
        'customer_id',
        'wash_bay_id',
        'assigned_attendant_id',
        'created_by',
        'work_order_id',
        'order_number',
        'wash_type',
        'status',
        'source',
        'priority',
        'queue_position',
        'notes',
        'queued_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'queue_position' => 'integer',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($washOrder) {
            if ($washOrder->status === 'queued' && !$washOrder->queue_position) {
                $washOrder->queue_position = static::getNextQueuePosition($washOrder->branch_id);
            }
        });
    }

    // Relationships
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function washBay(): BelongsTo
    {
        return $this->belongsTo(WashBay::class);
    }

    public function assignedAttendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_attendant_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WashOrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'reference_id')
            ->where('reference_type', 'wash_order');
    }

    // Scopes
    public function scopeQueued($query)
    {
        return $query->where('status', 'queued');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['queued', 'in_progress']);
    }

    public function scopeCombo($query)
    {
        return $query->where('source', 'combo');
    }

    public function scopeWalkIn($query)
    {
        return $query->where('source', 'walk_in');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeOrderByQueue($query)
    {
        return $query->orderBy('priority', 'desc')
            ->orderBy('queue_position', 'asc');
    }

    // Helpers
    public function isQueued(): bool
    {
        return $this->status === 'queued';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCombo(): bool
    {
        return $this->source === 'combo';
    }

    public function canStart(): bool
    {
        return $this->status === 'queued';
    }

    public function canComplete(): bool
    {
        return $this->status === 'in_progress';
    }

    public function start(?WashBay $bay = null): void
    {
        $updateData = [
            'status' => 'in_progress',
            'started_at' => now(),
        ];

        if ($bay) {
            $updateData['wash_bay_id'] = $bay->id;
            $bay->markAsOccupied();
        }

        $this->update($updateData);
        $this->reorderQueue();
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'queue_position' => null,
        ]);

        if ($this->washBay) {
            $this->washBay->markAsAvailable();
        }
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'queue_position' => null,
        ]);

        if ($this->washBay) {
            $this->washBay->markAsAvailable();
        }

        $this->reorderQueue();
    }

    public function prioritize(): void
    {
        $this->update(['priority' => 'priority']);
    }

    protected function reorderQueue(): void
    {
        $orders = static::where('branch_id', $this->branch_id)
            ->where('status', 'queued')
            ->orderBy('priority', 'desc')
            ->orderBy('queue_position', 'asc')
            ->get();

        $position = 1;
        foreach ($orders as $order) {
            $order->update(['queue_position' => $position++]);
        }
    }

    public static function getNextQueuePosition(int $branchId): int
    {
        $maxPosition = static::where('branch_id', $branchId)
            ->where('status', 'queued')
            ->max('queue_position');

        return ($maxPosition ?? 0) + 1;
    }

    // Calculations
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum('total');
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'queued' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'ghost',
        };
    }

    public function getSourceBadgeAttribute(): string
    {
        return match ($this->source) {
            'walk_in' => 'Walk-in',
            'combo' => 'Combo',
            'appointment' => 'Appointment',
            default => $this->source,
        };
    }
}
