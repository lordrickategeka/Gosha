<?php

namespace App\Domains\Marketplace\Notifications;

use App\Domains\Platform\Services\ApiIntegrationService;
use App\Domains\Platform\Services\CreamThemeConnector;
use Illuminate\Notifications\Notification;
use RuntimeException;

class SupplierWelcomeEmailChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $integration = app(ApiIntegrationService::class)->getIntegration('cream_theme');
        if (! $integration?->isActive()) {
            throw new RuntimeException('Supplier welcome email requires Cream Theme mail configuration.');
        }
        $message = 'Welcome to GarageHQ! Your supplier account has been created and your login email is verified. '
            .'A platform administrator will review your business before you can create listings or propose catalog products. '
            .'You can sign in and browse the catalog while approval is pending.';
        try {
            $response = app(CreamThemeConnector::class)->client($integration->credentials)->post('/api/mail/send', [
                'to' => $notifiable->email, 'subject' => 'GarageHQ supplier registration received',
                'text' => $message."\n\n".route('supplier.dashboard'),
                'html' => '<p>'.e($message).'</p><p><a href="'.e(route('supplier.dashboard')).'">Open your supplier dashboard</a></p>',
            ]);
            if (! $response->successful() || $response->json('status') !== 'sent') {
                throw new RuntimeException('Email was not accepted.');
            }
        } catch (\Throwable) {
            // Keep credentials, recipient identifiers and provider response bodies out of failed-job errors.
            throw new RuntimeException('Supplier welcome email failed. Check Cream Theme mail configuration and quota.');
        }
    }
}
