<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, HasAuditLog;

    protected $fillable = [
        'branch_id',
        'category_id',
        'supplier_id',
        'recorded_by',
        'approved_by',
        'expense_number',
        'amount',
        'payment_method',
        'reference',
        'description',
        'expense_date',
        'receipt_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (!$expense->expense_number) {
                $expense->expense_number = static::generateExpenseNumber();
            }
        });
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(?User $approver = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver?->id ?? auth()->id(),
        ]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public static function generateExpenseNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return sprintf('EXP-%s-%04d', $date, $count);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'error',
            default => 'ghost',
        };
    }
}
