<?php

namespace App\Domains\Expenses\Services;

use App\Domains\Expenses\Models\Expense;
use App\Domains\Expenses\Models\ExpenseApproval;
use App\Domains\Expenses\Models\ExpenseApprovalChain;
use App\Domains\Expenses\Models\ExpenseApprovalLevel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ExpenseApprovalService
{
    /**
     * Find the most appropriate approval chain for an expense
     */
    public function findApprovalChain(Expense $expense): ?ExpenseApprovalChain
    {
        // Check for auto-approval first
        if ($this->canAutoApprove($expense)) {
            return null;
        }

        // Find matching chains, ordered by specificity
        $chain = ExpenseApprovalChain::forExpense($expense)
            ->orderByRaw('
                CASE
                    WHEN category_id IS NOT NULL THEN 4
                    WHEN branch_id IS NOT NULL THEN 3
                    WHEN expense_type IS NOT NULL THEN 2
                    WHEN min_amount IS NOT NULL OR max_amount IS NOT NULL THEN 1
                    ELSE 0
                END DESC
            ')
            ->first();

        // Fallback to default chain if no specific match
        if (!$chain) {
            $chain = ExpenseApprovalChain::where('vendor_id', $expense->vendor_id)
                ->where('is_default', true)
                ->active()
                ->first();
        }

        return $chain;
    }

    /**
     * Check if expense qualifies for auto-approval
     */
    public function canAutoApprove(Expense $expense): bool
    {
        if (!$expense->category) {
            return false;
        }

        return $expense->category->canAutoApprove($expense->amount);
    }

    /**
     * Initialize approval process for an expense
     */
    public function initializeApprovalProcess(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            // Check auto-approval
            if ($this->canAutoApprove($expense)) {
                $expense->approve();
                return;
            }

            // Find approval chain
            $chain = $this->findApprovalChain($expense);

            if (!$chain) {
                // No approval chain found, auto-approve
                $expense->approve();
                return;
            }

            // Create approval records for all levels
            foreach ($chain->levels as $level) {
                $approvers = $level->getApprovers($expense);

                foreach ($approvers as $approver) {
                    ExpenseApproval::create([
                        'expense_id' => $expense->id,
                        'approval_level_id' => $level->id,
                        'approver_id' => $approver->id,
                        'status' => 'pending',
                    ]);
                }
            }

            // Update expense status
            $expense->update([
                'status' => 'pending_approval',
                'current_approval_level' => 1,
            ]);

            // Notify first level approvers
            $this->notifyApprovers($expense, 1);
        });
    }

    /**
     * Process an approval action
     */
    public function processApproval(ExpenseApproval $approval, string $action, ?string $comments = null): void
    {
        DB::transaction(function () use ($approval, $action, $comments) {
            $expense = $approval->expense;

            if ($action === 'approve') {
                $approval->approve($comments);

                // Check if all approvers at this level have approved
                if ($this->isLevelComplete($expense, $approval->approvalLevel)) {
                    $this->advanceToNextLevel($expense);
                }
            } elseif ($action === 'reject') {
                $approval->reject($comments);

                // Reject the entire expense
                $expense->reject($comments, $approval->approver);

                // Mark all pending approvals as skipped
                ExpenseApproval::where('expense_id', $expense->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'skipped']);
            }
        });
    }

    /**
     * Check if all required approvals at a level are complete
     */
    protected function isLevelComplete(Expense $expense, ExpenseApprovalLevel $level): bool
    {
        $approvalsAtLevel = ExpenseApproval::where('expense_id', $expense->id)
            ->where('approval_level_id', $level->id)
            ->get();

        if ($level->require_all) {
            // All approvers must approve
            return $approvalsAtLevel->every(fn($a) => $a->isApproved());
        } else {
            // At least one approval is enough
            return $approvalsAtLevel->contains(fn($a) => $a->isApproved());
        }
    }

    /**
     * Advance expense to next approval level or complete approval
     */
    protected function advanceToNextLevel(Expense $expense): void
    {
        $currentLevel = $expense->current_approval_level;

        // Get the approval chain
        $firstApproval = $expense->approvals()->first();
        if (!$firstApproval) {
            $expense->approve();
            return;
        }

        $chain = $firstApproval->approvalLevel->approvalChain;
        $nextLevel = $chain->levels()->where('level_number', $currentLevel + 1)->first();

        if (!$nextLevel) {
            // No more levels, approve the expense
            $expense->approve($expense->approvals()->approved()->first()->approver);
            return;
        }

        // Check if next level can be skipped
        if ($nextLevel->canSkipForExpense($expense)) {
            // Mark approvals at this level as skipped
            ExpenseApproval::where('expense_id', $expense->id)
                ->where('approval_level_id', $nextLevel->id)
                ->update(['status' => 'skipped']);

            // Recursively check next level
            $expense->update(['current_approval_level' => $nextLevel->level_number]);
            $this->advanceToNextLevel($expense);
            return;
        }

        // Move to next level
        $expense->update(['current_approval_level' => $nextLevel->level_number]);

        // Notify next level approvers
        $this->notifyApprovers($expense, $nextLevel->level_number);
    }

    /**
     * Notify approvers at a specific level
     */
    protected function notifyApprovers(Expense $expense, int $levelNumber): void
    {
        $approvals = ExpenseApproval::where('expense_id', $expense->id)
            ->whereHas('approvalLevel', fn($q) => $q->where('level_number', $levelNumber))
            ->where('status', 'pending')
            ->with('approver')
            ->get();

        foreach ($approvals as $approval) {
            // Send notification to approver
            // Notification::send($approval->approver, new ExpenseApprovalRequired($expense));

            // For now, we'll skip actual notification sending
            // You can implement this based on your notification preferences
        }
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovalsForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return ExpenseApproval::where('approver_id', $user->id)
            ->where('status', 'pending')
            ->with(['expense', 'expense.category', 'expense.createdBy'])
            ->get();
    }

    /**
     * Get approval history for an expense
     */
    public function getApprovalHistory(Expense $expense): array
    {
        $approvals = $expense->approvals()
            ->with(['approvalLevel', 'approver'])
            ->orderBy('created_at')
            ->get();

        $history = [];
        foreach ($approvals as $approval) {
            $level = $approval->approvalLevel->level_number;

            if (!isset($history[$level])) {
                $history[$level] = [
                    'level' => $level,
                    'description' => $approval->approvalLevel->description,
                    'approvals' => [],
                ];
            }

            $history[$level]['approvals'][] = [
                'approver' => $approval->approver->name,
                'status' => $approval->status,
                'comments' => $approval->comments,
                'timestamp' => $approval->approved_at ?? $approval->rejected_at ?? $approval->created_at,
            ];
        }

        return array_values($history);
    }

    /**
     * Preview approval path for an expense without creating records
     */
    public function previewApprovalPath(Expense $expense): ?array
    {
        if ($this->canAutoApprove($expense)) {
            return [
                'auto_approved' => true,
                'reason' => 'Amount below category auto-approval threshold',
            ];
        }

        $chain = $this->findApprovalChain($expense);

        if (!$chain) {
            return [
                'auto_approved' => true,
                'reason' => 'No approval chain configured',
            ];
        }

        $path = [];
        foreach ($chain->levels as $level) {
            $approvers = $level->getApprovers($expense);

            $path[] = [
                'level' => $level->level_number,
                'description' => $level->description,
                'approvers' => array_map(fn($u) => $u->name, $approvers),
                'require_all' => $level->require_all,
                'can_skip' => $level->canSkipForExpense($expense),
            ];
        }

        return [
            'auto_approved' => false,
            'chain_name' => $chain->name,
            'levels' => $path,
        ];
    }
}
