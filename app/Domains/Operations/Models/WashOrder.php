<?php

namespace App\Domains\Operations\Models;

use App\Domains\CRM\Models\Customer;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Operations\Enums\WashOrderPriority;
use App\Domains\Operations\Enums\WashOrderStatus;
use App\Shared\Traits\BelongsToBranch;
use App\Shared\Traits\GeneratesOrderNumber;
use App\Shared\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domains\Vehicles\Models\Vehicle;
use App\Models\User;
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
        'queued_at'      => 'datetime',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'status'         => WashOrderStatus::class,
        'priority'       => WashOrderPriority::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($washOrder) {
            $status = $washOrder->status instanceof WashOrderStatus
                ? $washOrder->status
                : WashOrderStatus::tryFrom($washOrder->status);

            if ($status === WashOrderStatus::Queued && !$washOrder->queue_position) {
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
        return $query->where('status', WashOrderStatus::Queued);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', WashOrderStatus::InProgress);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', WashOrderStatus::Completed);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [WashOrderStatus::Queued->value, WashOrderStatus::InProgress->value]);
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
        return $this->status === WashOrderStatus::Queued;
    }

    public function isInProgress(): bool
    {
        return $this->status === WashOrderStatus::InProgress;
    }

    public function isCompleted(): bool
    {
        return $this->status === WashOrderStatus::Completed;
    }

    public function isCombo(): bool
    {
        return $this->source === 'combo';
    }

    public function canStart(): bool
    {
        return $this->status === WashOrderStatus::Queued;
    }

    public function canComplete(): bool
    {
        return $this->status === WashOrderStatus::InProgress;
    }

    public function start(?WashBay $bay = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($bay) {
            $updateData = [
                'status'     => WashOrderStatus::InProgress,
                'started_at' => now(),
            ];

            if ($bay) {
                $updateData['wash_bay_id'] = $bay->id;
                $bay->markAsOccupied();
            }

            $this->update($updateData);
            $this->reorderQueue();
        });
    }

    public function complete(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->update([
                'status'         => WashOrderStatus::Completed,
                'completed_at'   => now(),
                'queue_position' => null,
            ]);

            if ($this->washBay) {
                $this->washBay->markAsAvailable();
            }

            $this->generateInvoice();
        });
    }

    protected function generateInvoice(): void
    {
        if ($this->invoice()->exists()) {
            return;
        }

        $subtotal = $this->items()->sum('total');

        $invoice = Invoice::create([
            'branch_id'       => $this->branch_id,
            'customer_id'     => $this->customer_id,
            'wash_order_id'   => $this->id,
            'created_by'      => $this->created_by,
            'type'            => 'wash',
            'status'          => 'draft',
            'subtotal'        => $subtotal,
            'tax_rate'        => 0,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total'           => $subtotal,
            'amount_paid'     => 0,
            'balance_due'     => $subtotal,
            'issue_date'      => now()->toDateString(),
            'due_date'        => now()->addDays(7)->toDateString(),
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
                'item_type'   => 'wash',
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'discount'    => $item->discount,
                'tax'         => 0,
                'total'       => $item->total,
            ]);
        }
    }

    public function cancel(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->update([
                'status'         => WashOrderStatus::Cancelled,
                'queue_position' => null,
            ]);

            if ($this->washBay) {
                $this->washBay->markAsAvailable();
            }

            $this->reorderQueue();
        });
    }

    public function prioritize(): void
    {
        $this->update(['priority' => WashOrderPriority::Priority]);
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
        if ($this->status instanceof WashOrderStatus) {
            return $this->status->color();
        }

        return 'ghost';
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
