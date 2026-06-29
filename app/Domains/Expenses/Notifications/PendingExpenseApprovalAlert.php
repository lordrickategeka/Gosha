<?php

namespace App\Domains\Expenses\Notifications;

use App\Domains\Expenses\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingExpenseApprovalAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Expense $expense) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Awaiting Your Approval')
            ->warning()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('An expense is waiting for your approval.')
            ->line('**Description:** ' . ($this->expense->description ?? 'N/A'))
            ->line('**Amount:** ' . number_format($this->expense->amount, 2))
            ->line('**Submitted by:** ' . ($this->expense->recordedBy?->name ?? 'N/A'))
            ->line('**Branch:** ' . ($this->expense->branch?->name ?? 'N/A'))
            ->action('Review Expense', url('/expenses/' . $this->expense->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'pending_expense_approval',
            'title'        => 'Expense Awaiting Approval',
            'message'      => number_format($this->expense->amount, 2) . ' expense submitted by ' . ($this->expense->recordedBy?->name ?? 'a team member') . ' needs approval',
            'domain'       => 'expenses',
            'severity'     => 'warning',
            'expense_id'   => $this->expense->id,
            'amount'       => $this->expense->amount,
            'description'  => $this->expense->description,
            'submitted_by' => $this->expense->recordedBy?->name,
            'branch_id'    => $this->expense->branch_id,
            'branch_name'  => $this->expense->branch?->name,
            'url'          => url('/expenses/' . $this->expense->id),
            'requires_action' => true,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'pending-expense-approval-alert';
    }
}
