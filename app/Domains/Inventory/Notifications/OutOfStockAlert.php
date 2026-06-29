<?php

namespace App\Domains\Inventory\Notifications;

use App\Domains\Inventory\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OutOfStockAlert extends Notification implements ShouldQueue
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
            ->subject('⚠️ URGENT: Out of Stock - ' . $this->item->name)
            ->error()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('**URGENT: Out of Stock Alert**')
            ->line('The following inventory item is now out of stock:')
            ->line('')
            ->line('**Item:** ' . $this->item->name)
            ->line('**SKU:** ' . ($this->item->sku ?? 'N/A'))
            ->line('**Category:** ' . $this->item->category?->name)
            ->line('**Branch:** ' . $this->item->branch?->name)
            ->line('**Type:** ' . ucfirst(str_replace('_', ' ', $this->item->item_type)))
            ->line('')
            ->line('**This item is currently unavailable and may affect ongoing operations.**')
            ->line('')
            ->line('**Immediate Action Required:**')
            ->line('• Check for stock at other branches')
            ->line('• Contact supplier for urgent reorder')
            ->line('• Notify relevant staff about unavailability')
            ->action('Manage Inventory', url('/inventory/' . $this->item->id))
            ->line('Please address this issue as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'out_of_stock',
            'title' => 'Out of Stock Alert',
            'message' => "{$this->item->name} is out of stock",
            'inventory_item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'sku' => $this->item->sku,
            'current_quantity' => $this->item->quantity,
            'unit' => $this->item->unit,
            'branch_id' => $this->item->branch_id,
            'branch_name' => $this->item->branch?->name,
            'category' => $this->item->category?->name,
            'item_type' => $this->item->item_type,
            'supplier_id' => $this->item->supplier_id,
            'supplier_name' => $this->item->supplier?->name,
            'url' => url('/inventory/' . $this->item->id),
            'severity' => 'error',
            'requires_action' => true,
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'out-of-stock-alert';
    }
}
