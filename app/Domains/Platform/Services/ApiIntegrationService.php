<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Models\ApiIntegration;
use App\Domains\Platform\Services\Contracts\IntegrationConnectorInterface;
use App\Domains\Platform\Services\EmailConnector;
use App\Domains\Platform\Services\FlutterwaveConnector;
use App\Domains\Platform\Services\WhatsAppConnector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ApiIntegrationService
{
    public function getIntegration(string $provider): ?ApiIntegration
    {
        return ApiIntegration::query()->where('provider', $provider)->first();
    }

    public function upsertIntegration(string $provider, array $credentials, bool $isActive, ?string $webhookUrl = null, ?string $webhookSecret = null): ApiIntegration
    {
        return DB::transaction(function () use ($provider, $credentials, $isActive, $webhookUrl, $webhookSecret) {
            $integration = ApiIntegration::query()->updateOrCreate(
                ['provider' => $provider],
                [
                    'credentials' => $credentials,
                    'is_active' => $isActive,
                    'webhook_url' => $webhookUrl,
                    'webhook_secret' => $webhookSecret,
                ]
            );

            $integration->logs()->create([
                'action' => 'save',
                'status' => 'success',
                'details' => ['provider' => $provider, 'is_active' => $isActive],
            ]);

            return $integration;
        });
    }

    public function toggleStatus(ApiIntegration $integration): ApiIntegration
    {
        $integration->update(['is_active' => !$integration->is_active]);

        $integration->logs()->create([
            'action' => 'toggle',
            'status' => 'success',
            'details' => ['is_active' => $integration->is_active],
        ]);

        return $integration->refresh();
    }

    public function testConnection(ApiIntegration $integration): array
    {
        $connector = $this->makeConnector($integration->provider);
        $result = $connector->test($integration->credentials);

        $integration->update([
            'last_tested_at' => now(),
            'last_error_message' => $result['success'] ? null : ($result['message'] ?? 'Connection test failed.'),
        ]);

        $integration->logs()->create([
            'action' => 'test',
            'status' => $result['success'] ? 'success' : 'failed',
            'details' => ['provider' => $integration->provider],
            'error_message' => $result['success'] ? null : ($result['message'] ?? null),
        ]);

        return $result;
    }

    public function sendTestMessage(ApiIntegration $integration, string $recipientPhone, string $message): array
    {
        if ($integration->provider !== 'whatsapp') {
            return [
                'success' => false,
                'message' => 'Test message is currently supported for WhatsApp only.',
            ];
        }

        $connector = $this->makeConnector($integration->provider);

        if (!method_exists($connector, 'sendTestMessage')) {
            return [
                'success' => false,
                'message' => 'This provider does not support sending test messages.',
            ];
        }

        /** @var WhatsAppConnector $connector */
        $result = $connector->sendTestMessage($integration->credentials, $recipientPhone, $message);

        $integration->logs()->create([
            'action' => 'send_test_message',
            'status' => $result['success'] ? 'success' : 'failed',
            'details' => [
                'provider' => $integration->provider,
                'recipient_phone' => $recipientPhone,
            ],
            'error_message' => $result['success'] ? null : ($result['message'] ?? null),
        ]);

        return $result;
    }

    public function initiateFlutterwaveTestPayment(
        ApiIntegration $integration,
        string $customerEmail,
        string $customerName,
        float $amount,
        string $currency,
        string $redirectUrl
    ): array {
        if ($integration->provider !== 'flutterwave') {
            return [
                'success' => false,
                'message' => 'Test payment is currently supported for Flutterwave only.',
            ];
        }

        $connector = $this->makeConnector($integration->provider);

        if (!method_exists($connector, 'initiateTestPayment')) {
            return [
                'success' => false,
                'message' => 'This provider does not support test payment initiation.',
            ];
        }

        $txRef = 'ghq_test_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(8));

        /** @var FlutterwaveConnector $connector */
        $result = $connector->initiateTestPayment(
            credentials: $integration->credentials,
            customerEmail: $customerEmail,
            customerName: $customerName,
            amount: $amount,
            currency: $currency,
            redirectUrl: $redirectUrl,
            txRef: $txRef
        );

        $integration->logs()->create([
            'action' => 'initiate_test_payment',
            'status' => $result['success'] ? 'success' : 'failed',
            'details' => [
                'provider' => $integration->provider,
                'customer_email' => $customerEmail,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'tx_ref' => $txRef,
                'checkout_link' => $result['checkout_link'] ?? null,
            ],
            'error_message' => $result['success'] ? null : ($result['message'] ?? null),
        ]);

        return $result;
    }

    private function makeConnector(string $provider): IntegrationConnectorInterface
    {
        return match ($provider) {
            'whatsapp' => new WhatsAppConnector(),
            'email' => new EmailConnector(),
            'flutterwave' => new FlutterwaveConnector(),
            default => throw new InvalidArgumentException('Unsupported provider: ' . $provider),
        };
    }
}
