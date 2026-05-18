<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, HasAuditLog;

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'created_by',
        'bill_number',
        'supplier_invoice_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'amount_paid',
        'balance_due',
        'status',
        'bill_date',
        'due_date',
        'description',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'bill_date' => 'date',
        'due_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            if (! $bill->bill_number) {
                $bill->bill_number = static::generateBillNumber();
            }
        });

        static::saving(function ($bill) {
            $bill->recalculateTotals();
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['received', 'partially_paid', 'overdue'])
            ->where('balance_due', '>', 0);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['received', 'partially_paid'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', today());
            });
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total') ?: $this->subtotal;
        $total = $subtotal + ($this->tax_amount ?? 0) - ($this->discount_amount ?? 0);
        $amountPaid = $this->payments()->where('status', 'completed')->sum('amount');
        $balanceDue = $total - $amountPaid;

        $this->subtotal = $subtotal;
        $this->total = $total;
        $this->amount_paid = $amountPaid;
        $this->balance_due = $balanceDue;

        if ($balanceDue <= 0 && $total > 0) {
            $this->status = 'paid';
        } elseif ($amountPaid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date < today() && in_array($this->status, ['received', 'partially_paid'])) {
            $this->status = 'overdue';
        }
    }

    public function recordPayment(array $payload): BillPayment
    {
        $payment = $this->payments()->create($payload + [
            'supplier_id' => $this->supplier_id,
        ]);

        $this->refresh();
        $this->recalculateTotals();
        $this->saveQuietly();

        return $payment;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' ||
            (in_array($this->status, ['received', 'partially_paid']) && $this->due_date && $this->due_date->isBefore(today()));
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->isOverdue() || ! $this->due_date) {
            return 0;
        }

        return $this->due_date->diffInDays(today());
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'ghost',
            'received' => 'info',
            'partially_paid' => 'warning',
            'paid' => 'success',
            'overdue' => 'error',
            'cancelled' => 'neutral',
            default => 'ghost',
        };
    }

    public static function generateBillNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return sprintf('BILL-%s-%04d', $date, $count);
    }
}
