<?php

namespace App\Domains\Finance\Services;

use App\Domains\Platform\Models\PlatformSetting;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorSubscription;
use App\Domains\Platform\Models\VendorUsageLog;
use App\Domains\Platform\Notifications\SubscriptionGraceStartedAlert;
use App\Domains\Platform\Notifications\SubscriptionLockedAlert;
use App\Domains\Platform\Notifications\SubscriptionPaymentReceivedAlert;
use App\Domains\Platform\Services\ApiIntegrationService;
use App\Domains\Platform\Services\FlutterwaveConnector;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Create a new subscription for a vendor
     */
    public function createSubscription(Vendor $vendor, PricingPlan $plan, array $options = []): VendorSubscription
    {
        return DB::transaction(function () use ($vendor, $plan, $options) {
            // Cancel any existing active subscription
            $vendor->activeSubscription?->cancel('Switched to new plan', true);

            $subscription = VendorSubscription::create([
                'vendor_id' => $vendor->id,
                'pricing_plan_id' => $plan->id,
                'status' => $plan->has_trial ? VendorSubscription::STATUS_TRIAL : VendorSubscription::STATUS_ACTIVE,
                'trial_ends_at' => $plan->has_trial ? now()->addDays($plan->trial_days) : null,
                'current_period_start' => now(),
                'current_period_end' => now()->addDays($plan->getBillingCycleDays()),
                'next_billing_date' => $plan->has_trial ? now()->addDays($plan->trial_days) : now()->addDays($plan->getBillingCycleDays()),
                'custom_base_price' => $options['custom_price'] ?? null,
                'custom_commission_rate' => $options['custom_commission'] ?? null,
                'discount_percent' => $options['discount_percent'] ?? 0,
                'discount_reason' => $options['discount_reason'] ?? null,
            ]);

            // Update vendor reference
            $vendor->update(['pricing_plan_id' => $plan->id]);

            // Generate setup fee invoice if applicable
            if ($plan->setup_fee > 0 && !($options['waive_setup_fee'] ?? false)) {
                $this->generateSetupFeeInvoice($subscription);
            }

            // No trial: payment for the first period is due now. Trial
            // subscriptions get their first invoice when the trial converts
            // (see handleTrialExpirations).
            if (!$plan->has_trial) {
                $this->generateSubscriptionInvoice($subscription);
            }

            return $subscription;
        });
    }

    /**
     * Generate subscription invoice for a billing period
     */
    public function generateSubscriptionInvoice(VendorSubscription $subscription): ?VendorPlatformInvoice
    {
        if (!$subscription->plan->hasSubscriptionComponent()) {
            return null;
        }

        $invoice = VendorPlatformInvoice::create([
            'vendor_id' => $subscription->vendor_id,
            'vendor_subscription_id' => $subscription->id,
            'type' => VendorPlatformInvoice::TYPE_SUBSCRIPTION,
            'status' => VendorPlatformInvoice::STATUS_PENDING,
            'period_start' => $subscription->current_period_start,
            'period_end' => $subscription->current_period_end,
            'currency' => $subscription->plan->currency,
            'issue_date' => now(),
            // Due immediately: the grace period (BILLING_GRACE_DAYS) is the buffer
            // applied *after* this date elapses unpaid, before lockdown — see
            // handleOverdueSubscriptions().
            'due_date' => now(),
        ]);

        $price = $subscription->getEffectivePrice();
        $cycleName = $subscription->plan->getBillingCycleLabel();

        $invoice->addItem(
            "{$subscription->plan->name} - {$cycleName}ly subscription",
            $price,
            'subscription',
            [
                'plan_id' => $subscription->plan->id,
                'period' => "{$subscription->current_period_start->format('Y-m-d')} to {$subscription->current_period_end->format('Y-m-d')}",
            ]
        );

        // Apply tax if configured
        $taxRate = PlatformSetting::get(PlatformSetting::PLATFORM_TAX_RATE, 0);
        if ($taxRate > 0) {
            $invoice->tax_amount = $price * ($taxRate / 100);
        }

        $invoice->calculateTotals();

        return $invoice;
    }

    /**
     * Generate commission invoice based on usage
     */
    public function generateCommissionInvoice(VendorSubscription $subscription, string $period = null): ?VendorPlatformInvoice
    {
        if (!$subscription->plan->hasCommissionComponent()) {
            return null;
        }

        $period = $period ?? now()->subMonth()->format('Y-m');
        $commissionRate = $subscription->getEffectiveCommissionRate();

        if ($commissionRate <= 0) {
            return null;
        }

        // Get unbilled usage for the period
        $usage = VendorUsageLog::where('vendor_id', $subscription->vendor_id)
            ->where('billing_period', $period)
            ->where('billed', false)
            ->get();

        if ($usage->isEmpty()) {
            return null;
        }

        $commissionBase = $subscription->plan->commission_base;
        $totalAmount = 0;
        $details = [];

        foreach ($usage as $log) {
            $commissionAmount = 0;

            switch ($commissionBase) {
                case 'gross_revenue':
                case 'net_revenue':
                case 'payment_received':
                    if ($log->metric === VendorUsageLog::METRIC_PAYMENT_RECEIVED) {
                        $commissionAmount = $log->amount * ($commissionRate / 100);
                        $details[] = [
                            'metric' => 'Payment',
                            'amount' => $log->amount,
                            'commission' => $commissionAmount,
                        ];
                    }
                    break;

                case 'work_order_total':
                    if ($log->metric === VendorUsageLog::METRIC_WORK_ORDER) {
                        $commissionAmount = $log->amount * ($commissionRate / 100);
                        $details[] = [
                            'metric' => 'Work Order',
                            'amount' => $log->amount,
                            'commission' => $commissionAmount,
                        ];
                    }
                    break;

                case 'wash_order_total':
                    if ($log->metric === VendorUsageLog::METRIC_WASH_ORDER) {
                        $commissionAmount = $log->amount * ($commissionRate / 100);
                        $details[] = [
                            'metric' => 'Wash Order',
                            'amount' => $log->amount,
                            'commission' => $commissionAmount,
                        ];
                    }
                    break;

                case 'invoice_total':
                    if ($log->metric === VendorUsageLog::METRIC_INVOICE) {
                        $commissionAmount = $log->amount * ($commissionRate / 100);
                        $details[] = [
                            'metric' => 'Invoice',
                            'amount' => $log->amount,
                            'commission' => $commissionAmount,
                        ];
                    }
                    break;
            }

            $totalAmount += $commissionAmount;
        }

        // Apply min/max caps
        if ($subscription->plan->commission_min && $totalAmount < $subscription->plan->commission_min) {
            $totalAmount = $subscription->plan->commission_min;
        }
        if ($subscription->plan->commission_max && $totalAmount > $subscription->plan->commission_max) {
            $totalAmount = $subscription->plan->commission_max;
        }

        if ($totalAmount <= 0) {
            return null;
        }

        $periodStart = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $periodEnd = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        $invoice = VendorPlatformInvoice::create([
            'vendor_id' => $subscription->vendor_id,
            'vendor_subscription_id' => $subscription->id,
            'type' => VendorPlatformInvoice::TYPE_COMMISSION,
            'status' => VendorPlatformInvoice::STATUS_PENDING,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'currency' => $subscription->plan->currency,
            'issue_date' => now(),
            // Due immediately: the grace period (BILLING_GRACE_DAYS) is the buffer
            // applied *after* this date elapses unpaid, before lockdown — see
            // handleOverdueSubscriptions().
            'due_date' => now(),
            'metadata' => ['details' => $details, 'commission_rate' => $commissionRate],
        ]);

        $invoice->addItem(
            "Platform commission ({$commissionRate}%) for {$periodStart->format('F Y')}",
            $totalAmount,
            'commission',
            ['rate' => $commissionRate, 'details' => $details]
        );

        $invoice->calculateTotals();

        // Mark usage as billed
        $usage->each(function ($log) use ($invoice) {
            $log->update(['billed' => true, 'platform_invoice_id' => $invoice->id]);
        });

        return $invoice;
    }

    /**
     * Generate usage-based invoice
     */
    public function generateUsageInvoice(VendorSubscription $subscription, string $period = null): ?VendorPlatformInvoice
    {
        if (!$subscription->plan->hasPayPerUseComponent()) {
            return null;
        }

        $period = $period ?? now()->subMonth()->format('Y-m');
        $plan = $subscription->plan;

        $usage = VendorUsageLog::where('vendor_id', $subscription->vendor_id)
            ->where('billing_period', $period)
            ->where('billed', false)
            ->selectRaw('metric, SUM(quantity) as total_quantity')
            ->groupBy('metric')
            ->get();

        if ($usage->isEmpty()) {
            return null;
        }

        $periodStart = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $periodEnd = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        $invoice = VendorPlatformInvoice::create([
            'vendor_id' => $subscription->vendor_id,
            'vendor_subscription_id' => $subscription->id,
            'type' => VendorPlatformInvoice::TYPE_USAGE,
            'status' => VendorPlatformInvoice::STATUS_PENDING,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'currency' => $plan->currency,
            'issue_date' => now(),
            // Due immediately: the grace period (BILLING_GRACE_DAYS) is the buffer
            // applied *after* this date elapses unpaid, before lockdown — see
            // handleOverdueSubscriptions().
            'due_date' => now(),
        ]);

        foreach ($usage as $log) {
            $unitPrice = match ($log->metric) {
                VendorUsageLog::METRIC_WORK_ORDER => $plan->price_per_work_order,
                VendorUsageLog::METRIC_WASH_ORDER => $plan->price_per_wash_order,
                VendorUsageLog::METRIC_INVOICE => $plan->price_per_invoice,
                VendorUsageLog::METRIC_USER_ADDED => $plan->price_per_user,
                VendorUsageLog::METRIC_BRANCH_ADDED => $plan->price_per_branch,
                VendorUsageLog::METRIC_SMS_SENT => $plan->price_per_sms,
                default => 0,
            };

            if ($unitPrice > 0) {
                $amount = $log->total_quantity * $unitPrice;
                $metricLabel = ucwords(str_replace('_', ' ', $log->metric)) . 's';

                $invoice->items()->create([
                    'description' => "{$log->total_quantity} {$metricLabel}",
                    'type' => 'usage',
                    'quantity' => $log->total_quantity,
                    'unit_price' => $unitPrice,
                    'amount' => $amount,
                    'metadata' => ['metric' => $log->metric],
                ]);
            }
        }

        $invoice->calculateTotals();

        // Mark usage as billed
        VendorUsageLog::where('vendor_id', $subscription->vendor_id)
            ->where('billing_period', $period)
            ->where('billed', false)
            ->update(['billed' => true, 'platform_invoice_id' => $invoice->id]);

        return $invoice;
    }

    /**
     * Generate setup fee invoice
     */
    public function generateSetupFeeInvoice(VendorSubscription $subscription): VendorPlatformInvoice
    {
        $invoice = VendorPlatformInvoice::create([
            'vendor_id' => $subscription->vendor_id,
            'vendor_subscription_id' => $subscription->id,
            'type' => 'setup_fee',
            'status' => VendorPlatformInvoice::STATUS_PENDING,
            'currency' => $subscription->plan->currency,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $invoice->addItem(
            "Setup fee - {$subscription->plan->name}",
            $subscription->plan->setup_fee,
            'setup_fee'
        );

        $invoice->calculateTotals();

        return $invoice;
    }

    /**
     * Process all due billing for a vendor
     */
    public function processBillingCycle(VendorSubscription $subscription): array
    {
        $invoices = [];

        // Check if subscription period has ended
        if ($subscription->current_period_end && $subscription->current_period_end->isPast()) {
            // Generate subscription invoice for the ending period
            if ($invoice = $this->generateSubscriptionInvoice($subscription)) {
                $invoices[] = $invoice;
            }

            // Renew the period
            $subscription->renew();
        }

        // Generate commission invoice for previous month
        if ($invoice = $this->generateCommissionInvoice($subscription)) {
            $invoices[] = $invoice;
        }

        // Generate usage invoice for previous month
        if ($invoice = $this->generateUsageInvoice($subscription)) {
            $invoices[] = $invoice;
        }

        return $invoices;
    }

    /**
     * Move active subscriptions with an overdue invoice into their grace
     * period, then lock the ones whose grace period has elapsed. "Locked" is
     * a derived state (status=past_due + grace_ends_at in the past), not a
     * separate status, so this is safe to re-run daily.
     *
     * @return array{0: int, 1: int} [newly past-due count, newly locked count]
     */
    public function handleOverdueSubscriptions(): array
    {
        VendorPlatformInvoice::where('status', VendorPlatformInvoice::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->update(['status' => VendorPlatformInvoice::STATUS_OVERDUE]);

        $newlyPastDue = 0;
        $newlyLocked = 0;

        VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)
            ->whereHas('invoices', fn ($query) => $query->where('status', VendorPlatformInvoice::STATUS_OVERDUE))
            ->with('vendor')
            ->get()
            ->each(function (VendorSubscription $subscription) use (&$newlyPastDue) {
                $oldestDueDate = $subscription->invoices()
                    ->where('status', VendorPlatformInvoice::STATUS_OVERDUE)
                    ->min('due_date');

                $graceEndsAt = Carbon::parse($oldestDueDate)->addDays($subscription->vendor->effectiveGraceDays());
                $subscription->markPastDue($graceEndsAt);
                $newlyPastDue++;

                $this->notifyVendorOwners($subscription->vendor, new SubscriptionGraceStartedAlert($subscription));
            });

        VendorSubscription::where('status', VendorSubscription::STATUS_PAST_DUE)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<', now())
            ->whereNull('locked_at')
            ->with('vendor')
            ->get()
            ->each(function (VendorSubscription $subscription) use (&$newlyLocked) {
                $subscription->markLocked();
                $newlyLocked++;

                $this->notifyVendorOwners($subscription->vendor, new SubscriptionLockedAlert($subscription));
            });

        return [$newlyPastDue, $newlyLocked];
    }

    /**
     * Convert trials whose trial_ends_at has passed into real billing. Free
     * plans just activate; paid plans activate and get an invoice due now,
     * which then flows through the normal handleOverdueSubscriptions()
     * pipeline above if it goes unpaid.
     */
    public function handleTrialExpirations(): int
    {
        $count = 0;

        VendorSubscription::where('status', VendorSubscription::STATUS_TRIAL)
            ->where('trial_ends_at', '<', now())
            ->with('plan')
            ->get()
            ->each(function (VendorSubscription $subscription) use (&$count) {
                $subscription->activate();

                if ($subscription->plan->billing_model !== PricingPlan::MODEL_FREE) {
                    $this->generateSubscriptionInvoice($subscription);
                }

                $count++;
            });

        return $count;
    }

    /**
     * Attempt to charge a subscription's saved Flutterwave card token for
     * renewal. Returns false on any failure (no token, gateway down, card
     * declined) so the caller can fall back to the normal invoice + grace
     * flow instead — auto-renewal has no separate failure path.
     */
    public function attemptAutoRenewal(VendorSubscription $subscription): bool
    {
        if (!$subscription->flutterwave_card_token) {
            return false;
        }

        $amount = $subscription->getEffectivePrice();
        if ($amount <= 0) {
            return false;
        }

        $integration = app(ApiIntegrationService::class)->getIntegration('flutterwave');
        if (!$integration || !$integration->isActive()) {
            return false;
        }

        $txRef = 'ghq_renew_' . $subscription->id . '_' . now()->format('YmdHis');

        $result = (new FlutterwaveConnector())->chargeToken(
            $integration->credentials,
            $subscription->flutterwave_card_token,
            $subscription->flutterwave_customer_email ?: $subscription->vendor->email,
            $amount,
            $subscription->plan->currency,
            $txRef
        );

        if (!($result['success'] ?? false)) {
            return false;
        }

        $invoice = $this->generateSubscriptionInvoice($subscription);
        if (!$invoice) {
            return false;
        }

        $invoice->recordPayment($amount, 'card', [
            'reference_number' => $txRef,
            'transaction_id' => $result['transaction_id'] ?? null,
        ]);

        $subscription->renew();

        return true;
    }

    /**
     * Verify a Flutterwave transaction and, if it's a successful payment for
     * a known invoice, record it and reactivate the subscription. Shared by
     * the payment-callback redirect and the webhook so both paths reconcile
     * the same way (idempotent — already-paid invoices are a no-op).
     */
    public function reconcileFlutterwaveTransaction(string $txRef, string $transactionId): array
    {
        $integration = app(ApiIntegrationService::class)->getIntegration('flutterwave');
        if (!$integration || !$integration->isActive()) {
            return ['success' => false, 'message' => 'Flutterwave is not configured.'];
        }

        $verification = (new FlutterwaveConnector())->verifyTransaction($integration->credentials, $transactionId);
        if (!($verification['success'] ?? false)) {
            return $verification;
        }

        if ($verification['tx_ref'] !== $txRef || $verification['status'] !== 'successful') {
            return ['success' => false, 'message' => 'Transaction could not be verified as successful.'];
        }

        $invoice = VendorPlatformInvoice::where('metadata->tx_ref', $txRef)->first();
        if (!$invoice) {
            return ['success' => false, 'message' => 'No matching invoice for this transaction.'];
        }

        if ($invoice->isPaid()) {
            return ['success' => true, 'message' => 'Invoice already paid.', 'invoice' => $invoice];
        }

        if (round((float) $verification['amount'], 2) < round((float) $invoice->balance_due, 2)
            || strtoupper($verification['currency']) !== strtoupper($invoice->currency)) {
            return ['success' => false, 'message' => 'Payment amount/currency does not match the amount due.'];
        }

        $paymentMethod = match (true) {
            str_contains($verification['payment_type'], 'card') => 'card',
            str_contains($verification['payment_type'], 'mobilemoney'), str_contains($verification['payment_type'], 'mobile_money') => 'mobile_money',
            default => 'other',
        };

        $invoice->recordPayment((float) $verification['amount'], $paymentMethod, [
            'reference_number' => $verification['flw_ref'],
            'transaction_id' => $transactionId,
        ]);

        if ($subscription = $invoice->subscription) {
            $subscription->reactivate();

            if (!empty($verification['card_token'])) {
                $subscription->update([
                    'flutterwave_card_token' => $verification['card_token'],
                    'flutterwave_customer_email' => $verification['customer_email'] ?: $subscription->flutterwave_customer_email,
                ]);
            }

            $this->notifyVendorOwners($subscription->vendor, new SubscriptionPaymentReceivedAlert($invoice));
        }

        return ['success' => true, 'message' => 'Payment confirmed.', 'invoice' => $invoice];
    }

    /**
     * Run the full daily billing cycle: convert expired trials, generate
     * renewal invoices (attempting auto-renewal first), then enforce
     * grace/lockdown on anything left unpaid. Safe to re-run.
     */
    public function runDailyBillingCycle(): array
    {
        $summary = [
            'trials_converted' => 0,
            'periods_processed' => 0,
            'auto_renewed' => 0,
            'newly_past_due' => 0,
            'newly_locked' => 0,
        ];

        $summary['trials_converted'] = $this->handleTrialExpirations();

        VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)
            ->where('current_period_end', '<=', now())
            ->with('plan', 'vendor')
            ->get()
            ->each(function (VendorSubscription $subscription) use (&$summary) {
                if ($subscription->auto_renew && $this->attemptAutoRenewal($subscription)) {
                    $summary['auto_renewed']++;
                    return;
                }

                $this->processBillingCycle($subscription);
                $summary['periods_processed']++;
            });

        [$summary['newly_past_due'], $summary['newly_locked']] = $this->handleOverdueSubscriptions();

        return $summary;
    }

    private function notifyVendorOwners(Vendor $vendor, $notification): void
    {
        $vendor->users()
            ->whereHas('roles', fn ($query) => $query->where('name', 'vendor-owner'))
            ->get()
            ->each(fn ($user) => $user->notify($notification));
    }

    /**
     * Change subscription plan
     */
    public function changePlan(VendorSubscription $subscription, PricingPlan $newPlan, bool $immediately = false): VendorSubscription
    {
        return DB::transaction(function () use ($subscription, $newPlan, $immediately) {
            if ($immediately) {
                // Prorate the current period
                // ... (complex proration logic would go here)

                $subscription->update([
                    'pricing_plan_id' => $newPlan->id,
                    'custom_base_price' => null,
                    'custom_commission_rate' => null,
                ]);

                $subscription->vendor->update(['pricing_plan_id' => $newPlan->id]);
            } else {
                // Schedule change at end of current period
                $subscription->update([
                    'cancel_at_period_end' => $subscription->current_period_end,
                    'cancellation_reason' => "Upgrading to {$newPlan->name}",
                ]);

                // Create new subscription to start at period end
                return $this->createSubscription($subscription->vendor, $newPlan, [
                    'start_date' => $subscription->current_period_end,
                ]);
            }

            return $subscription->fresh();
        });
    }
}
