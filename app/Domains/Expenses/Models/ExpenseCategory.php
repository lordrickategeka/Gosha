<?php

namespace App\Domains\Expenses\Models;

use App\Shared\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'code',
        'description',
        'expense_type',
        'color',
        'icon',
        'parent_id',
        'auto_approval_threshold',
        'requires_receipt',
        'requires_tax_invoice',
        'gl_account_code',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_receipt' => 'boolean',
        'requires_tax_invoice' => 'boolean',
        'auto_approval_threshold' => 'decimal:2',
    ];

    // Relationships
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class, 'parent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('expense_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // Helpers
    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    public function hasAutoApproval(): bool
    {
        return $this->auto_approval_threshold !== null;
    }

    public function canAutoApprove(float $amount): bool
    {
        if (!$this->hasAutoApproval()) {
            return false;
        }

        return $amount <= $this->auto_approval_threshold;
    }

    public function getTotalThisMonthAttribute(): float
    {
        $reportingAmountColumn = Expense::reportingAmountColumn();

        return $this->expenses()
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum($reportingAmountColumn);
    }

    public function getTotalThisYearAttribute(): float
    {
        $reportingAmountColumn = Expense::reportingAmountColumn();

        return $this->expenses()
            ->whereYear('expense_date', now()->year)
            ->sum($reportingAmountColumn);
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }

    public function getBadgeHtmlAttribute(): string
    {
        $style = "background-color: {$this->color}; color: white;";
        return "<span class=\"badge badge-sm\" style=\"{$style}\">{$this->name}</span>";
    }
}
