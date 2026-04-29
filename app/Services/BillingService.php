<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\VendorPlatformInvoice;
use App\Models\PricingPlan;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Models\VendorUsageLog;
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
            'due_date' => now()->addDays(PlatformSetting::get(PlatformSetting::BILLING_GRACE_DAYS, 7)),
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
            'due_date' => now()->addDays(PlatformSetting::get(PlatformSetting::BILLING_GRACE_DAYS, 7)),
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
            'due_date' => now()->addDays(PlatformSetting::get(PlatformSetting::BILLING_GRACE_DAYS, 7)),
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
     * Check and handle overdue subscriptions
     */
    public function handleOverdueSubscriptions(): void
    {
        $graceDays = PlatformSetting::get(PlatformSetting::BILLING_GRACE_DAYS, 7);
        $autoSuspend = PlatformSetting::get(PlatformSetting::BILLING_AUTO_SUSPEND, true);
        $suspendAfterDays = PlatformSetting::get(PlatformSetting::BILLING_SUSPEND_AFTER_DAYS, 14);

        // Mark invoices as overdue
        PlatformInvoice::where('status', PlatformInvoice::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->update(['status' => PlatformInvoice::STATUS_OVERDUE]);

        // Mark subscriptions as past due
        VendorSubscription::whereHas('invoices', function ($query) {
            $query->where('status', PlatformInvoice::STATUS_OVERDUE);
        })
        ->where('status', VendorSubscription::STATUS_ACTIVE)
        ->update(['status' => VendorSubscription::STATUS_PAST_DUE]);

        // Auto-suspend if enabled
        if ($autoSuspend) {
            $suspendDate = now()->subDays($suspendAfterDays);

            $toSuspend = VendorSubscription::where('status', VendorSubscription::STATUS_PAST_DUE)
                ->whereHas('invoices', function ($query) use ($suspendDate) {
                    $query->where('status', PlatformInvoice::STATUS_OVERDUE)
                          ->where('due_date', '<', $suspendDate);
                })
                ->get();

            foreach ($toSuspend as $subscription) {
                $subscription->update(['status' => VendorSubscription::STATUS_PAUSED]);
                $subscription->vendor->update(['is_active' => false]);
            }
        }
    }

    /**
     * Handle trial expirations
     */
    public function handleTrialExpirations(): void
    {
        $expiredTrials = VendorSubscription::where('status', VendorSubscription::STATUS_TRIAL)
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expiredTrials as $subscription) {
            // If plan is free, activate automatically
            if ($subscription->plan->billing_model === PricingPlan::MODEL_FREE) {
                $subscription->activate();
            } else {
                // Expire the trial
                $subscription->update(['status' => VendorSubscription::STATUS_EXPIRED]);

                // Generate first subscription invoice
                $this->generateSubscriptionInvoice($subscription);
            }
        }
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
