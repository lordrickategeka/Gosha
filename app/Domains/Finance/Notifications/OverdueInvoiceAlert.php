<?php

namespace App\Domains\Finance\Notifications;

use App\Domains\Finance\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueInvoiceAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Overdue Invoice: ' . $this->invoice->invoice_number)
            ->error()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Invoice ' . $this->invoice->invoice_number . ' is overdue.')
            ->line('**Customer:** ' . ($this->invoice->customer?->name ?? 'N/A'))
            ->line('**Amount Due:** ' . number_format($this->invoice->balance_due, 2))
            ->line('**Due Date:** ' . $this->invoice->due_date?->format('d M Y'))
            ->action('View Invoice', url('/invoices/' . $this->invoice->id));
    }

    public function toArray(object $notifiable): array
    {
        $daysOverdue = $this->invoice->due_date ? (int) today()->diffInDays($this->invoice->due_date, false) * -1 : 0;

        return [
            'type'          => 'overdue_invoice',
            'title'         => 'Overdue Invoice',
            'message'       => 'Invoice ' . $this->invoice->invoice_number . ' (' . ($this->invoice->customer?->name ?? 'unknown customer') . ') is ' . $daysOverdue . ' day(s) overdue',
            'domain'        => 'finance',
            'severity'      => $daysOverdue >= 14 ? 'error' : 'warning',
            'invoice_id'    => $this->invoice->id,
            'invoice_number'=> $this->invoice->invoice_number,
            'customer_name' => $this->invoice->customer?->name,
            'balance_due'   => $this->invoice->balance_due,
            'due_date'      => $this->invoice->due_date?->toDateString(),
            'days_overdue'  => $daysOverdue,
            'branch_id'     => $this->invoice->branch_id,
            'branch_name'   => $this->invoice->branch?->name,
            'url'           => url('/invoices/' . $this->invoice->id),
            'requires_action' => true,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'overdue-invoice-alert';
    }
}
