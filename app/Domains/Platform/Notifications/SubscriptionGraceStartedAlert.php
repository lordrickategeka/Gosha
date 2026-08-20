<?php

namespace App\Domains\Platform\Notifications;

use App\Domains\Platform\Models\VendorSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionGraceStartedAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected VendorSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $graceEndsAt = $this->subscription->grace_ends_at?->format('d M Y');

        return (new MailMessage)
            ->subject('Payment overdue — grace period started')
            ->error()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your subscription payment is overdue.')
            ->line('You have until **' . $graceEndsAt . '** to pay before your account access is restricted.')
            ->action('Pay Now', url('/billing'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_grace_started',
            'title' => 'Payment overdue',
            'message' => 'Your subscription payment is overdue. Pay before ' . $this->subscription->grace_ends_at?->format('d M Y') . ' to avoid losing access.',
            'domain' => 'platform',
            'severity' => 'warning',
            'subscription_id' => $this->subscription->id,
            'grace_ends_at' => $this->subscription->grace_ends_at?->toDateString(),
            'url' => url('/billing'),
            'requires_action' => true,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'subscription-grace-started-alert';
    }
}
