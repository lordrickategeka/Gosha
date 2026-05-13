<?php

namespace App\Services\Connectors;

use App\Services\Connectors\Contracts\IntegrationConnectorInterface;
use Illuminate\Support\Facades\Http;

class FlutterwaveConnector implements IntegrationConnectorInterface
{
    public function test(array $credentials): array
    {
        $required = ['public_key', 'secret_key'];

        foreach ($required as $key) {
            if (empty($credentials[$key])) {
                return [
                    'success' => false,
                    'message' => 'Missing required Flutterwave credential: ' . $key,
                ];
            }
        }

        $baseUrl = rtrim((string) ($credentials['base_url'] ?? 'https://api.flutterwave.com'), '/');
        $secretKey = (string) $credentials['secret_key'];
        $endpoint = $baseUrl . '/v3/balances';

        try {
            $response = Http::timeout(15)
                ->retry(1, 250)
                ->withToken($secretKey)
                ->acceptJson()
                ->get($endpoint);

            if ($response->successful()) {
                $statusText = strtolower((string) ($response->json('status') ?? ''));

                if ($statusText === 'success') {
                    return [
                        'success' => true,
                        'message' => 'Flutterwave API verified successfully.',
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Flutterwave API responded but did not confirm success.',
                ];
            }

            $errorMessage = (string) ($response->json('message')
                ?? $response->json('data.message')
                ?? $response->body()
                ?? 'Unknown Flutterwave API error.');

            if ($response->status() === 401 || $response->status() === 403) {
                return [
                    'success' => false,
                    'message' => 'Flutterwave authentication failed. Check secret key. ' . $errorMessage,
                ];
            }

            return [
                'success' => false,
                'message' => 'Flutterwave test failed: ' . $errorMessage,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Flutterwave test failed: ' . $e->getMessage(),
            ];
        }

    }

    public function initiateTestPayment(
        array $credentials,
        string $customerEmail,
        string $customerName,
        float $amount,
        string $currency,
        string $redirectUrl,
        string $txRef
    ): array {
        $required = ['public_key', 'secret_key'];

        foreach ($required as $key) {
            if (empty($credentials[$key])) {
                return [
                    'success' => false,
                    'message' => 'Missing required Flutterwave credential: ' . $key,
                ];
            }
        }

        $baseUrl = rtrim((string) ($credentials['base_url'] ?? 'https://api.flutterwave.com'), '/');
        $secretKey = (string) $credentials['secret_key'];
        $endpoint = $baseUrl . '/v3/payments';

        try {
            $response = Http::timeout(20)
                ->retry(1, 250)
                ->withToken($secretKey)
                ->acceptJson()
                ->post($endpoint, [
                    'tx_ref' => $txRef,
                    'amount' => $amount,
                    'currency' => strtoupper($currency),
                    'redirect_url' => $redirectUrl,
                    'customer' => [
                        'email' => $customerEmail,
                        'name' => $customerName,
                    ],
                    'customizations' => [
                        'title' => 'GarageHQ Integration Test',
                        'description' => 'Flutterwave API test payment',
                    ],
                ]);

            if ($response->successful() && strtolower((string) $response->json('status')) === 'success') {
                $checkoutLink = (string) ($response->json('data.link') ?? '');

                if ($checkoutLink === '') {
                    return [
                        'success' => false,
                        'message' => 'Flutterwave responded without a checkout link.',
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Flutterwave test payment initialized successfully.',
                    'checkout_link' => $checkoutLink,
                ];
            }

            $errorMessage = (string) ($response->json('message')
                ?? $response->json('data.message')
                ?? $response->body()
                ?? 'Unknown Flutterwave API error.');

            return [
                'success' => false,
                'message' => 'Failed to initialize Flutterwave test payment: ' . $errorMessage,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to initialize Flutterwave test payment: ' . $e->getMessage(),
            ];
        }
    }
}
