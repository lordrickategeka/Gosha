<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Services\ExpenseApprovalService;
use Livewire\Component;

class ExpenseView extends Component
{
    public Expense $expense;
    public $approvalHistory = [];

    public function mount(Expense $expense)
    {
        $this->expense = $expense->load([
            'category',
            'supplier',
            'createdBy',
            'approvedBy',
            'rejectedBy',
            'paidBy',
            'claimedBy',
            'branch',
            'attachments',
            'approvals.approver',
            'approvals.approvalLevel'
        ]);

        $approvalService = app(ExpenseApprovalService::class);
        $this->approvalHistory = $approvalService->getApprovalHistory($expense);
    }

    public function downloadAttachment($id)
    {
        $attachment = $this->expense->attachments()->findOrFail($id);
        return response()->download(storage_path('app/public/' . $attachment->file_path));
    }

    public function render()
    {
        return view('livewire.expenses.expense-view')
            ->layout('components.layouts.app', ['title' => 'Expense Details']);
    }
}
