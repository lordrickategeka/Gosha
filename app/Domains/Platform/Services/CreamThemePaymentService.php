<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Models\SubscriptionPaymentAttempt;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CreamThemePaymentService
{
    private function credentials(): array
    {
        $integration = app(ApiIntegrationService::class)->getIntegration('cream_theme');
        if (! $integration?->isActive() || ! in_array($integration->credentials['environment'] ?? '', ['test', 'live'])) {
            throw new RuntimeException('Online payments are unavailable. Please contact support.');
        }
        if (app()->environment('production') && $integration->credentials['environment'] !== 'live') {
            throw new RuntimeException('Live payments are not yet enabled. Please contact support.');
        }

        return $integration->credentials;
    }

    public function checkout(VendorPlatformInvoice $invoice, User $owner): string
    {
        abort_unless($owner->vendor_id === $invoice->vendor_id && app(SelfServiceSubscriptionService::class)->isOwner($owner), 403);
        abort_if($owner->vendor?->vendor_type === 'supplier' && ! $owner->vendor->is_verified_supplier, 403, 'Supplier approval is required before payment.');
        $lock = Cache::lock('invoice-checkout:'.$invoice->id, 45);
        if (! $lock->get()) {
            throw new RuntimeException('This payment is already being prepared. Please wait a moment.');
        }
        try {
            $invoice->refresh();
            if (! in_array($invoice->status, ['pending', 'overdue', 'partially_paid']) || $invoice->balance_due <= 0) {
                throw new RuntimeException('This invoice is not payable.');
            }
            $credentials = $this->credentials();
            $attempt = SubscriptionPaymentAttempt::where('invoice_id', $invoice->id)->whereIn('status', ['initiated', 'pending'])->first();
            $attempt ??= SubscriptionPaymentAttempt::create([
                'invoice_id' => $invoice->id, 'reference' => 'ghq-'.$invoice->id.'-'.Str::uuid(),
                'environment' => $credentials['environment'], 'amount' => $invoice->balance_due, 'currency' => $invoice->currency,
            ]);
            if ($attempt->environment !== $credentials['environment']) {
                throw new RuntimeException('This invoice has a payment in a different environment. Contact support before retrying.');
            }
            $response = app(CreamThemeConnector::class)->client($credentials)->post('/api/partner-keys/payments/initiate', [
                'amount' => (float) $attempt->amount, 'currency' => $attempt->currency,
                'customerEmail' => $owner->email, 'customerName' => $owner->name,
                'redirectUrl' => route('billing.pay.callback'), 'narration' => 'GarageHQ '.$invoice->invoice_number,
                'reference' => $attempt->reference, 'meta' => ['invoiceId' => $invoice->id, 'vendorId' => $invoice->vendor_id],
            ]);
            if (! $response->successful()) {
                throw new RuntimeException('Checkout could not be started. Please try again shortly.');
            }
            $data = $response->json();
            if (($data['environment'] ?? '') !== $attempt->environment || ($data['reference'] ?? '') !== $attempt->reference
                || round((float) ($data['amount'] ?? 0), 2) !== round((float) $attempt->amount, 2)
                || ($data['currency'] ?? '') !== $attempt->currency || empty($data['txRef'])) {
                throw new RuntimeException('Checkout details do not match the invoice. Please contact support.');
            }
            $attempt->update(['tx_ref' => $data['txRef'], 'status' => 'pending']);
            if (in_array($data['status'] ?? '', ['failed', 'cancelled'])) {
                $attempt->update(['status' => $data['status']]);
                throw new RuntimeException('This checkout was cancelled or failed. Select Pay again to start a new attempt.');
            }
            if (($data['status'] ?? '') === 'successful') {
                $this->reconcile($attempt);

                return route('billing.subscription');
            }
            $url = $data['checkoutUrl'] ?? '';
            if (parse_url($url, PHP_URL_SCHEME) !== 'https' || parse_url($url, PHP_URL_HOST) !== 'checkout.flutterwave.com') {
                throw new RuntimeException('No valid Flutterwave checkout link was returned.');
            }
            $attempt->update(['checkout_url' => $url]);

            return $url;
        } finally {
            $lock->release();
        }
    }

    public function reconcile(SubscriptionPaymentAttempt $attempt): bool
    {
        if ($attempt->confirmed_at) {
            return true;
        }
        if (! $attempt->tx_ref) {
            return false;
        }
        $credentials = $this->credentials();
        // Cream Theme verifies each transaction using its original environment.
        $response = app(CreamThemeConnector::class)->client($credentials)
            ->get('/api/partner-keys/payments/'.rawurlencode($attempt->tx_ref).'/verify');
        if (! $response->successful()) {
            return false;
        }
        $data = $response->json();
        if (($data['txRef'] ?? '') !== $attempt->tx_ref || ($data['reference'] ?? '') !== $attempt->reference
            || ($data['environment'] ?? '') !== $attempt->environment || ($data['currency'] ?? '') !== $attempt->currency
            || round((float) ($data['amount'] ?? 0), 2) !== round((float) $attempt->amount, 2)) {
            return false;
        }
        if (in_array($data['status'] ?? '', ['failed', 'cancelled'])) {
            $attempt->update(['status' => $data['status']]);

            return false;
        }
        if (($data['status'] ?? '') !== 'successful') {
            return false;
        }

        return DB::transaction(function () use ($attempt) {
            \App\Domains\Platform\Models\Vendor::whereKey($attempt->invoice->vendor_id)->lockForUpdate()->firstOrFail();
            // After the business lock, serialize different payment attempts on the invoice.
            $invoice = VendorPlatformInvoice::whereKey($attempt->invoice_id)->lockForUpdate()->firstOrFail();
            $attempt = SubscriptionPaymentAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($attempt->confirmed_at) {
                return true;
            }
            if ($invoice->isPaid()) {
                // A separate duplicate charge needs an operator review; don't silently grant more time.
                $attempt->update(['status' => 'review_required']);

                return false;
            }
            if (! in_array($invoice->status, ['pending', 'partially_paid', 'overdue'])
                || round((float) $invoice->balance_due, 2) !== round((float) $attempt->amount, 2)) {
                $attempt->update(['status' => 'review_required']);

                return false;
            }
            $invoice->recordPayment((float) $attempt->amount, 'other', ['reference_number' => $attempt->tx_ref,
                'transaction_id' => $attempt->tx_ref, 'notes' => 'Flutterwave via Cream Theme ('.$attempt->environment.')']);
            app(SelfServiceSubscriptionService::class)->activatePaidInvoice($invoice);
            $attempt->update(['status' => 'successful', 'confirmed_at' => now()]);

            return true;
        });
    }
}
