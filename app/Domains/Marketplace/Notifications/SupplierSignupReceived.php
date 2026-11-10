<?php

namespace App\Domains\Marketplace\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SupplierSignupReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct()
    {
        $this->onConnection('database')->afterCommit();
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function via(object $notifiable): array
    {
        return ['database', SupplierWelcomeEmailChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'supplier_signup_received', 'title' => 'Supplier registration received',
            'message' => 'Your email is verified. Platform approval is pending. Browse the catalog while your business is reviewed.',
            'domain' => 'marketplace', 'severity' => 'info', 'url' => route('supplier.dashboard'), 'requires_action' => false];
    }
}
