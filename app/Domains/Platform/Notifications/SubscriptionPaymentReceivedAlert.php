<?php

namespace App\Domains\Platform\Notifications;

use App\Domains\Platform\Models\VendorPlatformInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPaymentReceivedAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected VendorPlatformInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment received — thank you')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We received your payment for invoice ' . $this->invoice->invoice_number . '.')
            ->line('**Amount:** ' . number_format((float) $this->invoice->total, 2) . ' ' . $this->invoice->currency)
            ->line('Your account is fully active.')
            ->action('View Subscription', url('/billing'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_payment_received',
            'title' => 'Payment received',
            'message' => 'Payment received for invoice ' . $this->invoice->invoice_number . '. Your account is fully active.',
            'domain' => 'platform',
            'severity' => 'info',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'url' => url('/billing'),
            'requires_action' => false,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'subscription-payment-received-alert';
    }
}
