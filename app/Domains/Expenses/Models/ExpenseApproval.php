<?php

namespace App\Domains\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'approval_level_id',
        'approver_id',
        'status',
        'comments',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function approvalLevel(): BelongsTo
    {
        return $this->belongsTo(ExpenseApprovalLevel::class, 'approval_level_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
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

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForApprover($query, int $approverId)
    {
        return $query->where('approver_id', $approverId);
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

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(string $comments = null): void
    {
        $this->update([
            'status' => 'approved',
            'comments' => $comments,
            'approved_at' => now(),
        ]);
    }

    public function reject(string $comments = null): void
    {
        $this->update([
            'status' => 'rejected',
            'comments' => $comments,
            'rejected_at' => now(),
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge badge-warning badge-sm">Pending</span>',
            'approved' => '<span class="badge badge-success badge-sm">Approved</span>',
            'rejected' => '<span class="badge badge-error badge-sm">Rejected</span>',
            'skipped' => '<span class="badge badge-ghost badge-sm">Skipped</span>',
            default => '<span class="badge badge-ghost badge-sm">' . ucfirst($this->status) . '</span>',
        };
    }
}
