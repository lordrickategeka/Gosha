<?php

namespace App\Domains\Finance\Models;

use App\Domains\CRM\Models\Customer;
use App\Domains\Operations\Models\WashOrder;
use App\Domains\Operations\Models\WorkOrder;
use App\Models\User;
use App\Shared\Traits\BelongsToBranch;
use App\Shared\Traits\GeneratesOrderNumber;
use App\Shared\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, HasAuditLog, GeneratesOrderNumber;

    protected $orderNumberField = 'invoice_number';
    protected $orderNumberPrefix = 'INV';

    protected $fillable = [
        'branch_id',
        'customer_id',
        'work_order_id',
        'wash_order_id',
        'created_by',
        'invoice_number',
        'type',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'amount_paid',
        'balance_due',
        'status',
        'issue_date',
        'due_date',
        'notes',
        'terms',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($invoice) {
            $invoice->recalculateTotals();
        });
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function washOrder(): BelongsTo
    {
        return $this->belongsTo(WashOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'partial', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['sent', 'partial'])
                  ->where('due_date', '<', today());
            });
    }

    public function scopeToday($query)
    {
        return $query->whereDate('issue_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('issue_date', now()->month)
            ->whereYear('issue_date', now()->year);
    }

    // Helpers
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
            (in_array($this->status, ['sent', 'partial']) && $this->due_date < today());
    }

    public function canAcceptPayment(): bool
    {
        return in_array($this->status, ['sent', 'partial', 'overdue']) && $this->balance_due > 0;
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total') ?: $this->subtotal;
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $total = $subtotal + $taxAmount - ($this->discount_amount ?? 0);
        $amountPaid = $this->payments()->where('status', 'completed')->sum('amount');
        $balanceDue = $total - $amountPaid;

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total = $total;
        $this->amount_paid = $amountPaid;
        $this->balance_due = $balanceDue;

        // Update status based on payments
        if ($balanceDue <= 0) {
            $this->status = 'paid';
        } elseif ($amountPaid > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date < today() && in_array($this->status, ['sent', 'partial'])) {
            $this->status = 'overdue';
        }
    }

    public function send(): void
    {
        if ($this->status === 'draft') {
            $this->update(['status' => 'sent']);
        }
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'balance_due' => 0,
            'amount_paid' => $this->total,
        ]);
    }

    public function recordPayment(array $data): Payment
    {
        $payment = $this->payments()->create($data);

        // Recalculate and save
        $this->refresh();
        $this->recalculateTotals();
        $this->saveQuietly();

        return $payment;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'ghost',
            'sent' => 'info',
            'partial' => 'warning',
            'paid' => 'success',
            'overdue' => 'error',
            'cancelled' => 'error',
            'refunded' => 'secondary',
            default => 'ghost',
        };
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return $this->due_date->diffInDays(today());
    }
}
