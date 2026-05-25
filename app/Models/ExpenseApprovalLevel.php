<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseApprovalLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_chain_id',
        'level_number',
        'approver_role',
        'approver_user_id',
        'require_all',
        'can_skip',
        'skip_conditions',
        'timeout_hours',
    ];

    protected $casts = [
        'require_all' => 'boolean',
        'can_skip' => 'boolean',
        'skip_conditions' => 'array',
    ];

    // Relationships
    public function approvalChain(): BelongsTo
    {
        return $this->belongsTo(ExpenseApprovalChain::class, 'approval_chain_id');
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ExpenseApproval::class, 'approval_level_id');
    }

    // Helpers
    public function getApprovers(Expense $expense): array
    {
        if ($this->approver_user_id) {
            return [$this->approverUser];
        }

        // Get users by role
        $query = User::where('vendor_id', $expense->vendor_id);

        if ($expense->branch_id && $this->approver_role !== 'owner') {
            $query->where('branch_id', $expense->branch_id);
        }

        switch ($this->approver_role) {
            case 'manager':
                $query->role('manager');
                break;
            case 'admin':
                $query->role('admin');
                break;
            case 'finance_officer':
                $query->role('finance_officer');
                break;
            case 'owner':
                $query->role('owner');
                break;
        }

        return $query->get()->all();
    }

    public function canSkipForExpense(Expense $expense): bool
    {
        if (!$this->can_skip || !$this->skip_conditions) {
            return false;
        }

        // Evaluate skip conditions
        foreach ($this->skip_conditions as $condition => $value) {
            if ($condition === 'amount_below' && $expense->amount >= $value) {
                return false;
            }
            if ($condition === 'amount_above' && $expense->amount <= $value) {
                return false;
            }
        }

        return true;
    }

    public function getDescriptionAttribute(): string
    {
        if ($this->approverUser) {
            return $this->approverUser->name;
        }

        return ucfirst(str_replace('_', ' ', $this->approver_role));
    }
}
