<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\GeneratesOrderNumber;
use App\Traits\HasAuditLog;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\QualityCheck;
use App\Models\DebitNote;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, HasAuditLog, GeneratesOrderNumber, HasUuid;

    protected $orderNumberPrefix = 'WO';

    protected $fillable = [
        'branch_id',
        'vehicle_id',
        'customer_id',
        'service_bay_id',
        'assigned_technician_id',
        'created_by',
        'order_number',
        'type',
        'status',
        'is_combo',
        'priority',
        'mileage_in',
        'mileage_out',
        'customer_notes',
        'vehicle_items_left',
        'technician_notes',
        'estimated_completion',
        'checked_in_at',
        'started_at',
        'completed_at',
        'delivered_at',
    ];

    protected $casts = [
        'is_combo' => 'boolean',
        'mileage_in' => 'integer',
        'mileage_out' => 'integer',
        'vehicle_items_left' => 'array',
        'estimated_completion' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceBay(): BelongsTo
    {
        return $this->belongsTo(ServiceBay::class);
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function debitNotes(): HasMany
    {
        return $this->hasMany(DebitNote::class)->latest();
    }

    public function laborItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class)->where('item_type', 'labor');
    }

    public function partItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class)->where('item_type', 'part');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class)->orderByDesc('version');
    }

    public function latestQuotation(): HasOne
    {
        return $this->hasOne(Quotation::class)->latestOfMany('version');
    }

    public function washOrder(): HasOne
    {
        return $this->hasOne(WashOrder::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'reference_id')
            ->where('reference_type', 'work_order');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'quality_check', 'ready']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCombo($query)
    {
        return $query->where('is_combo', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
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

    // Helpers
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function hasApprovedQuotation(): bool
    {
        return $this->quotations()->where('status', 'approved')->exists();
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'delivered';
    }

    public function canStart(): bool
    {
        if (!in_array($this->status, ['open', 'quoted'])) {
            return false;
        }

        // If a quotation exists it must be approved before work can start
        $latest = $this->latestQuotation;
        if ($latest && !$latest->isApproved()) {
            return false;
        }

        return true;
    }

    public function canComplete(): bool
    {
        return in_array($this->status, ['in_progress', 'quality_check']);
    }

    public function canDeliver(): bool
    {
        return $this->status === 'ready';
    }

    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        if ($this->serviceBay) {
            $this->serviceBay->markAsOccupied();
        }
    }

    public function moveToQualityCheck(): void
    {
        $this->update(['status' => 'quality_check']);
    }

    public function markReady(): void
    {
        $this->update([
            'status' => 'ready',
            'completed_at' => now(),
        ]);

        if ($this->serviceBay) {
            $this->serviceBay->markAsAvailable();
        }

        $this->generateInvoice();
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
            'work_order_id'   => $this->id,
            'created_by'      => $this->created_by,
            'type'            => 'service',
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
                'item_type'   => $item->item_type,
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'discount'    => $item->discount,
                'tax'         => 0,
                'total'       => $item->total,
            ]);
        }
    }

    public function deliver(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Update vehicle mileage
        if ($this->mileage_out) {
            $this->vehicle->updateMileage($this->mileage_out);
        }
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);

        if ($this->serviceBay) {
            $this->serviceBay->markAsAvailable();
        }
    }

    protected function createWashOrder(): WashOrder
    {
        return WashOrder::create([
            'branch_id' => $this->branch_id,
            'vehicle_id' => $this->vehicle_id,
            'customer_id' => $this->customer_id,
            'work_order_id' => $this->id,
            'wash_type' => 'standard',
            'status' => 'queued',
            'source' => 'combo',
            'priority' => 'normal',
            'queued_at' => now(),
        ]);
    }

    // Calculations
    public function getLaborTotalAttribute(): float
    {
        return $this->laborItems->sum('total');
    }

    public function getPartsTotalAttribute(): float
    {
        return $this->partItems->sum('total');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum('total');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'info',
            'quoted' => 'accent',
            'in_progress' => 'warning',
            'quality_check' => 'secondary',
            'ready' => 'success',
            'delivered' => 'success',
            'cancelled' => 'error',
            default => 'ghost',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'ghost',
            'normal' => 'info',
            'high' => 'warning',
            'urgent' => 'error',
            default => 'ghost',
        };
    }


    /**
 * Get the quality check for this work order
 */
public function qualityCheck(): HasOne
{
    return $this->hasOne(QualityCheck::class);
}

/**
 * Check if work order can proceed to invoicing
 */
public function canProceedToInvoicing(): bool
{
    // Must have quality check
    if (!$this->qualityCheck) {
        return false;
    }

    // Quality check must be passed
    if ($this->qualityCheck->status !== 'passed') {
        return false;
    }

    // Status must be ready or delivered
    return in_array($this->status, ['ready', 'delivered']);
}

/**
 * Check if work order requires quality check
 */
public function requiresQualityCheck(): bool
{
    // Quality check is mandatory for all work orders
    return true;
}

/**
 * Check if quality check is pending
 */
public function hasQualityCheckPending(): bool
{
    return $this->status === 'quality_check' &&
           (!$this->qualityCheck || $this->qualityCheck->status !== 'passed');
}
}
