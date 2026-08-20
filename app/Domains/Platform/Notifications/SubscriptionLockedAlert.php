<?php

namespace App\Domains\Platform\Notifications;

use App\Domains\Platform\Models\VendorSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionLockedAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected VendorSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Account access restricted — payment required')
            ->error()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your grace period has ended and your account access has been restricted because a subscription payment is still outstanding.')
            ->action('Pay Now to Restore Access', url('/billing'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_locked',
            'title' => 'Account access restricted',
            'message' => 'Your grace period has ended. Pay now to restore full access.',
            'domain' => 'platform',
            'severity' => 'error',
            'subscription_id' => $this->subscription->id,
            'url' => url('/billing'),
            'requires_action' => true,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'subscription-locked-alert';
    }
}
