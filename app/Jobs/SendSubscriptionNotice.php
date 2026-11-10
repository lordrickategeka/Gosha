<?php

namespace App\Jobs;

use App\Domains\Platform\Models\SubscriptionNotice;
use App\Domains\Platform\Services\ApiIntegrationService;
use App\Domains\Platform\Services\CreamThemeConnector;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class SendSubscriptionNotice implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 45;

    public int $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return 'subscription-notice-'.$this->noticeId;
    }

    public function __construct(public int $noticeId) {}

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(CreamThemeConnector $connector): void
    {
        $lock = Cache::lock('subscription-notice:'.$this->noticeId, 60);
        if (! $lock->get()) {
            return;
        }
        try {
            $notice = SubscriptionNotice::find($this->noticeId);
            if (! $notice || $notice->sent_at || $notice->available_at->isFuture()) {
                return;
            }
            $notice->increment('attempts');
            $integration = app(ApiIntegrationService::class)->getIntegration('cream_theme');
            if (! $integration?->isActive()) {
                throw new RuntimeException('Configure Cream Theme transactional email.');
            }
            $response = $connector->client($integration->credentials)->post('/api/mail/send', [
                'to' => $notice->user->email, 'subject' => $notice->subject,
                'text' => $notice->message."\n\n".route('billing.subscription'),
                'html' => '<p>'.e($notice->message).'</p><p><a href="'.e(route('billing.subscription')).'">Open GarageHQ Billing</a></p>',
            ]);
            if (! $response->successful() || $response->json('status') !== 'sent') {
                throw new RuntimeException('Transactional email was not accepted. Check Cream Theme mail configuration and quotas.');
            }
            $notice->update(['sent_at' => now(), 'last_error' => null]);
        } catch (\Throwable $exception) {
            if (isset($notice) && $notice) {
                $notice->update(['last_error' => 'Email delivery failed. Check Cream Theme connection, sender and quota.', 'available_at' => now()->addMinutes(15)]);
            }
            // Never include upstream responses, credentials or recipient identifiers in logs.
            throw new RuntimeException('Subscription email delivery failed.');
        } finally {
            $lock->release();
        }
    }
}
