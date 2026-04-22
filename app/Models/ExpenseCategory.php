<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getTotalThisMonthAttribute(): float
    {
        return $this->expenses()
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
    }
}
