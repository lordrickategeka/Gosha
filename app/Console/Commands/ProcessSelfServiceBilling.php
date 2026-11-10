<?php

namespace App\Console\Commands;

use App\Domains\Platform\Models\SubscriptionNotice;
use App\Domains\Platform\Models\SubscriptionPaymentAttempt;
use App\Domains\Platform\Services\CreamThemePaymentService;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use App\Jobs\SendSubscriptionNotice;
use Illuminate\Console\Command;

class ProcessSelfServiceBilling extends Command
{
    protected $signature = 'billing:self-service';

    protected $description = 'Expire trials, reconcile pending hosted payments and queue subscription email';

    public function handle(SelfServiceSubscriptionService $subscriptions, CreamThemePaymentService $payments): int
    {
        $subscriptions->tick();
        SubscriptionPaymentAttempt::whereIn('status', ['initiated', 'pending'])->where('updated_at', '<=', now()->subMinute())
            ->orderBy('updated_at')->limit(100)->get()->each(function ($attempt) use ($payments) {
                try {
                    if (! $attempt->tx_ref) {
                        $owner = $attempt->invoice->vendor->users()->whereHas('roles', fn ($query) => $query->where('name', $attempt->invoice->vendor->vendor_type === 'supplier' ? 'supplier_admin' : 'vendor-owner'))->first();
                        if ($owner) {
                            $payments->checkout($attempt->invoice, $owner);
                        }
                    } else {
                        $payments->reconcile($attempt);
                    }
                } catch (\Throwable) {
                    // Retain pending state for recovery; do not log upstream bodies or credentials.
                } finally {
                    $attempt->touch();
                }
            });
        SubscriptionNotice::whereNull('sent_at')->where('available_at', '<=', now())->where('attempts', '<', 10)
            ->orderBy('id')->limit(100)->get()->each(function ($notice) {
                try {
                    SendSubscriptionNotice::dispatch($notice->id);
                } catch (\Throwable) {
                    // A synchronous queue also leaves the durable outbox available for retry.
                }
            });
        $this->info('Self-service billing processed.');

        return self::SUCCESS;
    }
}
