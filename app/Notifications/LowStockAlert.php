<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected InventoryItem $item;

    /**
     * Create a new notification instance.
     */
    public function __construct(InventoryItem $item)
    {
        $this->item = $item;
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
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->item->name)
            ->warning()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('**Low Stock Alert**')
            ->line('The following inventory item has reached low stock level:')
            ->line('')
            ->line('**Item:** ' . $this->item->name)
            ->line('**SKU:** ' . ($this->item->sku ?? 'N/A'))
            ->line('**Category:** ' . $this->item->category?->name)
            ->line('**Current Stock:** ' . number_format($this->item->quantity, 2) . ' ' . $this->item->unit)
            ->line('**Reorder Level:** ' . number_format($this->item->reorder_level, 2) . ' ' . $this->item->unit)
            ->line('**Branch:** ' . $this->item->branch?->name)
            ->line('')
            ->line('Please consider reordering this item to avoid stockouts.')
            ->action('View Inventory', url('/inventory/' . $this->item->id))
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'title' => 'Low Stock Alert',
            'message' => "{$this->item->name} is running low on stock",
            'inventory_item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'sku' => $this->item->sku,
            'current_quantity' => $this->item->quantity,
            'reorder_level' => $this->item->reorder_level,
            'unit' => $this->item->unit,
            'branch_id' => $this->item->branch_id,
            'branch_name' => $this->item->branch?->name,
            'category' => $this->item->category?->name,
            'url' => url('/inventory/' . $this->item->id),
            'severity' => 'warning',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'low-stock-alert';
    }
}
