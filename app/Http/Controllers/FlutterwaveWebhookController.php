<?php

namespace App\Http\Controllers;

use App\Domains\Finance\Services\BillingService;
use App\Domains\Platform\Services\ApiIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $integration = app(ApiIntegrationService::class)->getIntegration('flutterwave');

        if (!$integration || !$integration->isActive() || !$integration->webhook_secret) {
            return response('Webhook not configured', 200);
        }

        $signature = (string) $request->header('verif-hash', '');
        if ($signature === '' || !hash_equals($integration->webhook_secret, $signature)) {
            return response('Invalid signature', 401);
        }

        $event = (string) $request->input('event');
        $data = (array) $request->input('data', []);

        if ($event === 'charge.completed' && strtolower((string) ($data['status'] ?? '')) === 'successful') {
            app(BillingService::class)->reconcileFlutterwaveTransaction(
                (string) ($data['tx_ref'] ?? ''),
                (string) ($data['id'] ?? '')
            );
        }

        return response('OK', 200);
    }
}
