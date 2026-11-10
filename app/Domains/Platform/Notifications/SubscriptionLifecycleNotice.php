<?php

namespace App\Domains\Platform\Notifications;

use App\Domains\Platform\Models\SubscriptionNotice;
use Illuminate\Notifications\Notification;

class SubscriptionLifecycleNotice extends Notification
{
    public function __construct(public SubscriptionNotice $notice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => $this->notice->event, 'title' => $this->notice->subject,
            'message' => $this->notice->message, 'domain' => 'platform', 'severity' => 'info',
            'url' => route('billing.subscription'), 'requires_action' => true];
    }
}
