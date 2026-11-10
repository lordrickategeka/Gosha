<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Models\PlatformSetting;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\SubscriptionNotice;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use App\Domains\Platform\Models\VendorSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SelfServiceSubscriptionService
{
    public function audience(?Vendor $vendor): string
    {
        return $vendor?->vendor_type === 'supplier' ? 'supplier' : 'garage';
    }

    public function isOwner(\App\Models\User $user): bool
    {
        return $this->audience($user->vendor) === 'supplier' ? $user->hasRole('supplier_admin') : $user->hasRole('vendor-owner');
    }

    public function current(?Vendor $vendor): ?VendorSubscription
    {
        return $vendor?->subscriptions()->latest('id')->first();
    }

    public function available(PricingPlan $plan): bool
    {
        return $plan->is_active && ($plan->is_trial || ($plan->hasSubscriptionComponent() && $plan->base_price > 0));
    }

    public function start(Vendor $vendor, PricingPlan $plan): VendorSubscription
    {
        if (! $this->available($plan) || $plan->audience !== $this->audience($vendor)) {
            throw ValidationException::withMessages(['selectedPlanId' => 'Choose a trial or a paid subscription plan.']);
        }
        if ($plan->is_trial && $vendor->subscriptions()->exists()) {
            throw ValidationException::withMessages(['selectedPlanId' => 'The free trial is available only once per business.']);
        }
        $awaitingApproval = $vendor->vendor_type === 'supplier' && ! $vendor->is_verified_supplier;
        $subscription = VendorSubscription::create([
            'vendor_id' => $vendor->id, 'pricing_plan_id' => $plan->id, 'self_service' => true,
            'status' => $plan->is_trial && ! $awaitingApproval ? 'trial' : 'paused', 'auto_renew' => false,
            'trial_ends_at' => $plan->is_trial && ! $awaitingApproval ? now()->addDays(14) : null,
            'current_period_start' => $plan->is_trial && ! $awaitingApproval ? now() : null,
            'current_period_end' => $plan->is_trial && ! $awaitingApproval ? now()->addDays(14) : null,
        ]);
        $vendor->update(['pricing_plan_id' => $plan->id, 'trial_ends_at' => $subscription->trial_ends_at]);
        if (! $plan->is_trial && ! $awaitingApproval) {
            $this->purchaseInvoice($subscription, $plan);
        }
        if ($vendor->vendor_type === 'supplier' && $plan->is_trial && ! $awaitingApproval) {
            $this->notice($subscription, 'trial-started', 'Your supplier trial has started',
                'Your free supplier trial ends on '.$subscription->trial_ends_at->format('d M Y H:i T').'. Marketplace commission is waived during the trial. No card or automatic charge.');
        }

        return $subscription;
    }

    public function approveSupplier(Vendor $vendor): void
    {
        if ($vendor->vendor_type !== 'supplier' || ! $vendor->is_verified_supplier) {
            return;
        }
        DB::transaction(function () use ($vendor) {
            Vendor::whereKey($vendor->id)->lockForUpdate()->firstOrFail();
            $subscription = $this->current($vendor);
            if (! $subscription?->self_service || $subscription->status !== 'paused' || $subscription->last_payment_at || $subscription->trial_ends_at) {
                return;
            }
            if ($subscription->plan->is_trial) {
                $end = now()->addDays(14);
                $subscription->update(['status' => 'trial', 'current_period_start' => now(), 'current_period_end' => $end, 'trial_ends_at' => $end]);
                $vendor->update(['trial_ends_at' => $end]);
                $this->notice($subscription, 'trial-started', 'Your supplier trial has started',
                    'Your supplier business is approved. Your free trial ends on '.$end->format('d M Y H:i T').'. Subscription fees and marketplace commission are waived during the trial. No card or automatic charge.');
            } else {
                $this->purchaseInvoice($subscription, $subscription->plan);
                $this->notice($subscription, 'supplier-approved', 'Your supplier business is approved', 'Your subscription invoice is ready in Billing. Complete payment to activate selling.');
            }
        });
    }

    public function purchaseInvoice(VendorSubscription $subscription, PricingPlan $plan): VendorPlatformInvoice
    {
        if ($plan->is_trial || ! $this->available($plan) || $plan->audience !== $this->audience($subscription->vendor)) {
            throw ValidationException::withMessages(['plan' => 'Choose an available paid subscription. Trials cannot be restarted.']);
        }
        if ($subscription->vendor->vendor_type === 'supplier' && ! $subscription->vendor->is_verified_supplier) {
            throw ValidationException::withMessages(['plan' => 'Your supplier business must be approved before payment.']);
        }

        return DB::transaction(function () use ($subscription, $plan) {
            $vendor = Vendor::whereKey($subscription->vendor_id)->lockForUpdate()->firstOrFail();
            if ($vendor->vendor_type === 'supplier') {
                app(\App\Domains\Marketplace\Services\SupplierPlanAccess::class)->assertPlanFits($vendor, $plan);
            }
            VendorSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail();
            // One unpaid quote at a time. Selecting again reuses it instead of duplicating charges.
            $existing = $subscription->invoices()->unpaid()->whereNotNull('metadata->target_plan_id')->first();
            if ($existing) {
                if ((int) $existing->metadata['target_plan_id'] !== $plan->id) {
                    throw ValidationException::withMessages(['plan' => 'An invoice is already awaiting payment. Complete it before choosing another plan.']);
                }

                return $existing;
            }
            $price = (float) $plan->base_price;
            // Setup fees apply only on the first paid activation, not on every renewal.
            $setup = $subscription->last_payment_at ? 0 : (float) $plan->setup_fee;
            $tax = round($price * (float) PlatformSetting::get(PlatformSetting::PLATFORM_TAX_RATE, 0) / 100, 2);
            $invoice = VendorPlatformInvoice::create([
                'invoice_number' => 'GHQ-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
                'vendor_id' => $subscription->vendor_id, 'vendor_subscription_id' => $subscription->id,
                'type' => 'subscription', 'status' => 'pending', 'currency' => strtoupper($plan->currency),
                'issue_date' => now(), 'due_date' => now(), 'tax_amount' => $tax,
                'metadata' => ['target_plan_id' => $plan->id, 'billing_cycle_days' => $plan->getBillingCycleDays(),
                    'plan_name' => $plan->name, 'self_service' => true],
            ]);
            $invoice->addItem($plan->name.' subscription - '.$plan->getBillingCycleLabel(), $price, 'subscription');
            if ($setup > 0) {
                $invoice->addItem('Setup fee', $setup, 'setup_fee');
            }
            $invoice->calculateTotals();

            return $invoice;
        });
    }

    public function activatePaidInvoice(VendorPlatformInvoice $invoice): void
    {
        if ($invoice->type === 'commission' && ! empty($invoice->metadata['marketplace_transaction_id'])) {
            \App\Domains\Marketplace\Models\MarketplaceTransaction::whereKey($invoice->metadata['marketplace_transaction_id'])->update(['status' => 'settled']);
            if ($invoice->subscription) {
                $this->notice($invoice->subscription, 'commission-paid-'.$invoice->id, 'Marketplace commission payment confirmed', 'Your marketplace commission invoice '.$invoice->invoice_number.' has been paid. This payment does not change your subscription period.');
            }

            return;
        }
        $subscription = VendorSubscription::whereKey($invoice->vendor_subscription_id)->lockForUpdate()->firstOrFail();
        $metadata = $invoice->metadata ?? [];
        if (empty($metadata['self_service']) || empty($metadata['target_plan_id'])) {
            $subscription->reactivate();

            return;
        }
        $start = now();
        // Prepayment preserves remaining trial time or paid time on the same plan.
        if ($subscription->trial_ends_at?->isFuture() && $subscription->status === 'trial') {
            $start = $subscription->trial_ends_at->copy();
        } elseif ($subscription->status === 'active' && $subscription->pricing_plan_id === (int) $metadata['target_plan_id'] && $subscription->current_period_end?->isFuture()) {
            $start = $subscription->current_period_end->copy();
        }
        $end = $start->copy()->addDays((int) $metadata['billing_cycle_days']);
        $subscription->update([
            'self_service' => true,
            'pricing_plan_id' => $metadata['target_plan_id'], 'status' => 'active',
            'current_period_start' => now(), 'current_period_end' => $end,
            'next_billing_date' => $end, 'last_payment_at' => now(),
            'grace_ends_at' => null, 'locked_at' => null, 'auto_renew' => false,
        ]);
        $subscription->vendor->update(['pricing_plan_id' => $metadata['target_plan_id'], 'status' => 'active']);
        $invoice->update(['period_start' => $start, 'period_end' => $end]);
        $this->notice($subscription, 'payment-'.$invoice->id, 'Payment confirmed',
            'Your payment for '.$metadata['plan_name'].' is confirmed. Your access is paid through '.$end->format('d M Y H:i T').'. No automatic charge is enabled.');
    }

    public function restricted(VendorSubscription $subscription): bool
    {
        if (! $subscription->self_service) {
            return false;
        }

        return match ($subscription->status) {
            'trial' => ! $subscription->trial_ends_at || $subscription->trial_ends_at->lessThanOrEqualTo(now()),
            'active' => ! $subscription->current_period_end || $subscription->current_period_end->lessThanOrEqualTo(now()),
            default => true,
        };
    }

    public function tick(): void
    {
        VendorSubscription::where('self_service', true)->where('status', 'paused')->whereNull('trial_ends_at')->whereNull('last_payment_at')
            ->whereHas('vendor', fn ($query) => $query->where('vendor_type', 'supplier')->where('is_verified_supplier', true))
            ->each(fn ($subscription) => $this->approveSupplier($subscription->vendor));
        VendorSubscription::where('self_service', true)->whereIn('status', ['trial', 'active'])->chunkById(100, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                DB::transaction(function () use ($subscription) {
                    $subscription = VendorSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail();
                    if ($this->restricted($subscription)) {
                        $trial = $subscription->status === 'trial';
                        $subscription->update(['status' => 'expired']);
                        $this->notice($subscription, ($trial ? 'trial-expired' : 'period-expired').'-'.$subscription->current_period_end?->timestamp,
                            $trial ? 'Your GarageHQ trial has ended' : 'Your subscription needs renewal',
                            'Your data is safe. Choose and pay for a subscription from Billing to restore access. Suppliers can still fulfil existing orders.');
                    } elseif ($subscription->status === 'trial') {
                        $hours = now()->diffInHours($subscription->trial_ends_at, false);
                        foreach ([3, 1] as $days) {
                            // Do not send a late three-day reminder alongside the one-day reminder.
                            if ($hours <= $days * 24 && ($days === 1 || $hours > 24)) {
                                $this->notice($subscription, 'trial-reminder-'.$days, 'Your GarageHQ trial ends soon',
                                    'Your free trial ends on '.$subscription->trial_ends_at->format('d M Y H:i T').'. Choose a paid plan in Billing to continue. No card or automatic charge is enabled.');
                            }
                        }
                    }
                });
            }
        });
    }

    public function notice(VendorSubscription $subscription, string $event, string $subject, string $message): void
    {
        foreach ($subscription->vendor->users()->whereHas('roles', fn ($query) => $query->where('name', $subscription->vendor->vendor_type === 'supplier' ? 'supplier_admin' : 'vendor-owner'))->get() as $owner) {
            $notice = SubscriptionNotice::firstOrCreate(['dedupe_key' => $subscription->id.':'.$owner->id.':'.$event], [
                'vendor_subscription_id' => $subscription->id, 'user_id' => $owner->id,
                'event' => $event, 'subject' => $subject, 'message' => $message, 'available_at' => now(),
            ]);
            if ($notice->wasRecentlyCreated) {
                $owner->notify(new \App\Domains\Platform\Notifications\SubscriptionLifecycleNotice($notice));
                // Delivery is driven by the durable outbox, independently of signup success.
            }
        }
    }
}
