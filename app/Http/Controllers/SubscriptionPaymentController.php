<?php

namespace App\Http\Controllers;

use App\Domains\Finance\Services\BillingService;
use App\Domains\Platform\Models\VendorPlatformInvoice;
use App\Domains\Platform\Services\ApiIntegrationService;
use App\Domains\Platform\Services\FlutterwaveConnector;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionPaymentController extends Controller
{
    public function pay(Request $request, VendorPlatformInvoice $invoice)
    {
        abort_unless($invoice->vendor_id === $request->user()->vendor_id, 403);

        if ($invoice->isPaid()) {
            return redirect()->route('billing.subscription')->with('error', 'This invoice is already paid.');
        }

        $integration = app(ApiIntegrationService::class)->getIntegration('flutterwave');
        if (!$integration || !$integration->isActive()) {
            return back()->with('error', 'Online payments are not currently available. Please contact support.');
        }

        $txRef = 'ghq_inv_' . $invoice->id . '_' . now()->format('YmdHis');
        $invoice->update(['metadata' => array_merge($invoice->metadata ?? [], ['tx_ref' => $txRef])]);

        $result = (new FlutterwaveConnector())->initiateCheckout($integration->credentials, [
            'tx_ref' => $txRef,
            'amount' => $invoice->balance_due,
            'currency' => $invoice->currency,
            'redirect_url' => route('billing.pay.callback'),
            'customer_email' => $request->user()->email,
            'customer_name' => $request->user()->name,
            'title' => 'GarageHQ Subscription',
            'description' => $invoice->invoice_number,
            'meta' => ['invoice_id' => $invoice->id, 'vendor_id' => $invoice->vendor_id],
        ]);

        if (!($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? 'Failed to start payment.');
        }

        return redirect()->away($result['checkout_link']);
    }

    public function callback(Request $request)
    {
        $txRef = (string) $request->query('tx_ref');
        $transactionId = (string) $request->query('transaction_id');
        $status = (string) $request->query('status');

        if ($txRef === '' || $transactionId === '' || $status !== 'successful') {
            return redirect()->route('billing.subscription')->with('error', 'Payment was not completed.');
        }

        $result = app(BillingService::class)->reconcileFlutterwaveTransaction($txRef, $transactionId);

        if ($result['success'] ?? false) {
            return redirect()->route('billing.subscription')->with('success', 'Payment confirmed — your subscription is active.');
        }

        return redirect()->route('billing.subscription')->with(
            'error',
            $result['message'] ?? 'We could not confirm your payment yet. It may take a moment — please check back shortly.'
        );
    }

    /**
     * Minimal data export offered while a vendor's account is locked, so
     * they aren't cut off from their own customer list while sorting out
     * payment. Gated by the billing_lockdown_export_enabled platform setting.
     */
    public function exportData(Request $request): StreamedResponse
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        $filename = 'customers-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($vendor) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Phone', 'Email', 'Address', 'Customer Since']);

            $vendor->customers()->orderBy('name')->chunk(200, function ($customers) use ($handle) {
                foreach ($customers as $customer) {
                    fputcsv($handle, [
                        $customer->name,
                        $customer->phone,
                        $customer->email,
                        $customer->address,
                        $customer->created_at?->format('Y-m-d'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
