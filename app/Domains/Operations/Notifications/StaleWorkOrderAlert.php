<?php

namespace App\Domains\Operations\Notifications;

use App\Domains\Operations\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaleWorkOrderAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected WorkOrder $workOrder) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $days = (int) $this->workOrder->created_at->diffInDays(now());

        return (new MailMessage)
            ->subject('Work Order Stalled: ' . ($this->workOrder->order_number ?? '#' . $this->workOrder->id))
            ->warning()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A work order has been open for ' . $days . ' days without being delivered.')
            ->line('**Order:** ' . ($this->workOrder->order_number ?? '#' . $this->workOrder->id))
            ->line('**Customer:** ' . ($this->workOrder->customer?->name ?? 'N/A'))
            ->line('**Status:** ' . ucfirst($this->workOrder->status))
            ->action('View Work Order', url('/work-orders/' . $this->workOrder->id));
    }

    public function toArray(object $notifiable): array
    {
        $days = (int) $this->workOrder->created_at->diffInDays(now());

        return [
            'type'          => 'stale_work_order',
            'title'         => 'Stalled Work Order',
            'message'       => 'Work order ' . ($this->workOrder->order_number ?? '#' . $this->workOrder->id) . ' has been open for ' . $days . ' days',
            'domain'        => 'operations',
            'severity'      => $days >= 7 ? 'error' : 'warning',
            'work_order_id' => $this->workOrder->id,
            'order_number'  => $this->workOrder->order_number,
            'status'        => $this->workOrder->status,
            'days_open'     => $days,
            'customer_name' => $this->workOrder->customer?->name,
            'branch_id'     => $this->workOrder->branch_id,
            'branch_name'   => $this->workOrder->branch?->name,
            'url'           => url('/work-orders/' . $this->workOrder->id),
            'requires_action' => true,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'stale-work-order-alert';
    }
}
