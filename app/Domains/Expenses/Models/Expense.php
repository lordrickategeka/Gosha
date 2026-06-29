<?php

namespace App\Domains\Expenses\Models;

use App\Models\User;
use App\Shared\Traits\BelongsToBranch;
use App\Shared\Traits\BelongsToVendor;
use App\Shared\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Expense extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch, BelongsToVendor, HasAuditLog;

    protected $fillable = [
        'vendor_id',
        'branch_id',
        'expense_type',
        'category_id',
        'supplier_id',
        'expense_number',
        'expense_date',
        'amount',
        'currency',
        'exchange_rate',
        'amount_in_base_currency',
        'tax_amount',
        'tax_percentage',
        'tax_inclusive',
        'total_amount',
        'payment_method',
        'payment_reference',
        'description',
        'status',
        'current_approval_level',
        'is_recurring',
        'recurrence_config',
        'parent_expense_id',
        'claimed_by',
        'recorded_by',
        'approved_by',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'paid_by',
        'paid_at',
        'metadata',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_in_base_currency' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'total_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'recurrence_config' => 'array',
        'metadata' => 'array',
        'expense_date' => 'date',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (!$expense->recorded_by && ($expense->created_by || auth()->id())) {
                $expense->recorded_by = $expense->created_by ?? auth()->id();
            }

            if (!$expense->created_by && ($expense->recorded_by || auth()->id())) {
                $expense->created_by = $expense->recorded_by ?? auth()->id();
            }

            if (!$expense->expense_number) {
                $expense->expense_number = static::generateExpenseNumber();
            }

            // Auto-calculate amount in base currency
            if ($expense->currency !== 'UGX') {
                $expense->amount_in_base_currency = $expense->amount * $expense->exchange_rate;
            } else {
                $expense->amount_in_base_currency = $expense->amount;
            }

            // Auto-calculate total amount
            if ($expense->tax_amount) {
                $expense->total_amount = $expense->tax_inclusive
                    ? $expense->amount
                    : $expense->amount + $expense->tax_amount;
            } else {
                $expense->total_amount = $expense->amount;
            }
        });

        static::updating(function ($expense) {
            // Recalculate on update
            if ($expense->isDirty(['amount', 'exchange_rate', 'currency'])) {
                if ($expense->currency !== 'UGX') {
                    $expense->amount_in_base_currency = $expense->amount * $expense->exchange_rate;
                } else {
                    $expense->amount_in_base_currency = $expense->amount;
                }
            }

            if ($expense->isDirty(['amount', 'tax_amount', 'tax_inclusive'])) {
                if ($expense->tax_amount) {
                    $expense->total_amount = $expense->tax_inclusive
                        ? $expense->amount
                        : $expense->amount + $expense->tax_amount;
                } else {
                    $expense->total_amount = $expense->amount;
                }
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function parentExpense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'parent_expense_id');
    }

    public function childExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'parent_expense_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ExpenseApproval::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ExpenseAttachment::class);
    }

    public static function hasVendorIdColumn(): bool
    {
        return Schema::hasColumn((new static)->getTable(), 'vendor_id');
    }

    public static function reportingAmountColumn(): string
    {
        return Schema::hasColumn((new static)->getTable(), 'amount_in_base_currency')
            ? 'amount_in_base_currency'
            : 'amount';
    }

    public function scopeForVendor($query, int $vendorId)
    {
        $table = $query->getModel()->getTable();

        if (Schema::hasColumn($table, 'vendor_id')) {
            return $query->where($table . '.vendor_id', $vendorId);
        }

        return $query->select($table . '.*')
            ->join('branches', $table . '.branch_id', '=', 'branches.id')
            ->where('branches.vendor_id', $vendorId);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
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

    public function scopeThisYear($query)
    {
        return $query->whereYear('expense_date', now()->year);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('expense_type', $type);
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true)->whereNull('parent_expense_id');
    }

    public function scopeAwaitingMyApproval($query, int $userId)
    {
        return $query->where('status', 'pending_approval')
            ->whereHas('approvals', function ($q) use ($userId) {
                $q->where('approver_id', $userId)
                  ->where('status', 'pending');
            });
    }

    // Status Checkers
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Type Checkers
    public function isBusinessExpense(): bool
    {
        return $this->expense_type === 'business';
    }

    public function isPettyCash(): bool
    {
        return $this->expense_type === 'petty_cash';
    }

    public function isEmployeeClaim(): bool
    {
        return $this->expense_type === 'employee_claim';
    }

    public function isPayroll(): bool
    {
        return $this->expense_type === 'payroll';
    }

    // Approval Management
    public function submitForApproval(): void
    {
        $this->update([
            'status' => 'pending_approval',
            'current_approval_level' => 1,
        ]);

        // Logic to create approval records will be in ExpenseApprovalService
    }

    public function approve(?User $approver = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver?->id ?? auth()->id(),
        ]);
    }

    public function reject(string $reason = null, ?User $rejector = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_by' => $rejector?->id ?? auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsPaid(?User $payer = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_by' => $payer?->id ?? auth()->id(),
            'paid_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    // Helpers
    public static function generateExpenseNumber(): string
    {
        $date = now()->format('Ymd');
        $vendorId = auth()->user()->vendor_id ?? 1;
        $table = (new static)->getTable();
        $count = static::query()
            ->forVendor($vendorId)
            ->whereDate($table . '.created_at', today())
            ->count() + 1;
        return sprintf('EXP-%s-%04d', $date, $count);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'ghost',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'rejected' => 'error',
            'paid' => 'info',
            'cancelled' => 'ghost',
            default => 'ghost',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge badge-ghost badge-sm">Draft</span>',
            'pending_approval' => '<span class="badge badge-warning badge-sm">Pending</span>',
            'approved' => '<span class="badge badge-success badge-sm">Approved</span>',
            'rejected' => '<span class="badge badge-error badge-sm">Rejected</span>',
            'paid' => '<span class="badge badge-info badge-sm">Paid</span>',
            'cancelled' => '<span class="badge badge-ghost badge-sm">Cancelled</span>',
            default => '<span class="badge badge-ghost badge-sm">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_amount, 2);
    }

    public function hasReceipt(): bool
    {
        return $this->attachments()->where('attachment_type', 'receipt')->exists();
    }

    public function hasInvoice(): bool
    {
        return $this->attachments()->where('attachment_type', 'invoice')->exists();
    }

    public function getCurrentApprovalLevel(): ?ExpenseApprovalLevel
    {
        // Will be implemented with ExpenseApprovalService
        return null;
    }

    public function getPendingApprovers(): array
    {
        // Will be implemented with ExpenseApprovalService
        return [];
    }
}
