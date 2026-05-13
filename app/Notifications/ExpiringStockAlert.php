<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiringStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected InventoryItem $item;
    protected int $daysUntilExpiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(InventoryItem $item)
    {
        $this->item = $item;
        $this->daysUntilExpiry = $item->expiry_date
            ? now()->diffInDays($item->expiry_date, false)
            : 0;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isExpired = $this->daysUntilExpiry < 0;
        $subject = $isExpired
            ? 'Expired Stock: ' . $this->item->name
            : 'Stock Expiring Soon: ' . $this->item->name;

        $message = (new MailMessage)
            ->subject($subject)
            ->warning()
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($isExpired) {
            $message->line('**EXPIRED STOCK ALERT**')
                ->line('The following inventory item has expired:');
        } else {
            $message->line('**EXPIRING STOCK ALERT**')
                ->line('The following inventory item will expire soon:');
        }

        return $message
            ->line('')
            ->line('**Item:** ' . $this->item->name)
            ->line('**SKU:** ' . ($this->item->sku ?? 'N/A'))
            ->line('**Category:** ' . $this->item->category?->name)
            ->line('**Quantity:** ' . number_format($this->item->quantity, 2) . ' ' . $this->item->unit)
            ->line('**Expiry Date:** ' . $this->item->expiry_date?->format('M d, Y'))
            ->line('**Days Until Expiry:** ' . ($isExpired ? 'EXPIRED' : abs($this->daysUntilExpiry) . ' days'))
            ->line('**Branch:** ' . $this->item->branch?->name)
            ->line('')
            ->line($isExpired
                ? 'Please remove this expired item from inventory.'
                : 'Consider using or discounting this item before expiration.')
            ->action('View Inventory', url('/inventory/' . $this->item->id))
            ->line('Thank you for maintaining inventory quality.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isExpired = $this->daysUntilExpiry < 0;

        return [
            'type' => $isExpired ? 'expired_stock' : 'expiring_stock',
            'title' => $isExpired ? 'Expired Stock' : 'Stock Expiring Soon',
            'message' => $isExpired
                ? "{$this->item->name} has expired"
                : "{$this->item->name} expires in {$this->daysUntilExpiry} days",
            'inventory_item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'sku' => $this->item->sku,
            'quantity' => $this->item->quantity,
            'unit' => $this->item->unit,
            'expiry_date' => $this->item->expiry_date?->toDateString(),
            'days_until_expiry' => $this->daysUntilExpiry,
            'is_expired' => $isExpired,
            'branch_id' => $this->item->branch_id,
            'branch_name' => $this->item->branch?->name,
            'category' => $this->item->category?->name,
            'url' => url('/inventory/' . $this->item->id),
            'severity' => $isExpired ? 'error' : 'warning',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return $this->daysUntilExpiry < 0 ? 'expired-stock-alert' : 'expiring-stock-alert';
    }
}
