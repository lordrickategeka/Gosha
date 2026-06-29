<?php

namespace App\Domains\Expenses\Models;

use App\Shared\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseApprovalChain extends Model
{
    use HasFactory, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'expense_type',
        'category_id',
        'branch_id',
        'min_amount',
        'max_amount',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(ExpenseApprovalLevel::class, 'approval_chain_id')->orderBy('level_number');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForExpense($query, Expense $expense)
    {
        return $query->where('vendor_id', $expense->vendor_id)
            ->active()
            ->where(function ($q) use ($expense) {
                $q->whereNull('expense_type')
                  ->orWhere('expense_type', $expense->expense_type);
            })
            ->where(function ($q) use ($expense) {
                $q->whereNull('category_id')
                  ->orWhere('category_id', $expense->category_id);
            })
            ->where(function ($q) use ($expense) {
                $q->whereNull('branch_id')
                  ->orWhere('branch_id', $expense->branch_id);
            })
            ->where(function ($q) use ($expense) {
                $q->where(function ($subQ) use ($expense) {
                    $subQ->whereNull('min_amount')
                         ->orWhere('min_amount', '<=', $expense->amount);
                })
                ->where(function ($subQ) use ($expense) {
                    $subQ->whereNull('max_amount')
                         ->orWhere('max_amount', '>=', $expense->amount);
                });
            });
    }

    // Helpers
    public function getLevelCount(): int
    {
        return $this->levels()->count();
    }

    public function appliesToExpense(Expense $expense): bool
    {
        // Type match
        if ($this->expense_type && $this->expense_type !== $expense->expense_type) {
            return false;
        }

        // Category match
        if ($this->category_id && $this->category_id !== $expense->category_id) {
            return false;
        }

        // Branch match
        if ($this->branch_id && $this->branch_id !== $expense->branch_id) {
            return false;
        }

        // Amount range match
        if ($this->min_amount && $expense->amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $expense->amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    public function getApplicabilityDescription(): string
    {
        $parts = [];

        if ($this->expense_type) {
            $parts[] = ucfirst(str_replace('_', ' ', $this->expense_type)) . ' expenses';
        }

        if ($this->category) {
            $parts[] = $this->category->name;
        }

        if ($this->branch) {
            $parts[] = $this->branch->name;
        }

        if ($this->min_amount || $this->max_amount) {
            $amountRange = 'Amount: ';
            if ($this->min_amount && $this->max_amount) {
                $amountRange .= number_format($this->min_amount) . ' - ' . number_format($this->max_amount);
            } elseif ($this->min_amount) {
                $amountRange .= 'Above ' . number_format($this->min_amount);
            } else {
                $amountRange .= 'Below ' . number_format($this->max_amount);
            }
            $parts[] = $amountRange;
        }

        return empty($parts) ? 'All expenses' : implode(' | ', $parts);
    }
}
