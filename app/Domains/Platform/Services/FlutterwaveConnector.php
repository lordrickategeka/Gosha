<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Services\Contracts\IntegrationConnectorInterface;
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

    /**
     * Start a real (non-test) checkout for a platform invoice payment.
     * $payload keys: tx_ref, amount, currency, redirect_url, customer_email,
     * customer_name, title, description, meta (array, optional).
     */
    public function initiateCheckout(array $credentials, array $payload): array
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
        $endpoint = $baseUrl . '/v3/payments';

        try {
            $response = Http::timeout(20)
                ->retry(1, 250)
                ->withToken($secretKey)
                ->acceptJson()
                ->post($endpoint, [
                    'tx_ref' => $payload['tx_ref'],
                    'amount' => $payload['amount'],
                    'currency' => strtoupper((string) $payload['currency']),
                    'redirect_url' => $payload['redirect_url'],
                    'customer' => [
                        'email' => $payload['customer_email'],
                        'name' => $payload['customer_name'] ?? null,
                    ],
                    'customizations' => [
                        'title' => $payload['title'] ?? 'GarageHQ Subscription',
                        'description' => $payload['description'] ?? 'Subscription payment',
                    ],
                    'meta' => $payload['meta'] ?? [],
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
                    'checkout_link' => $checkoutLink,
                ];
            }

            $errorMessage = (string) ($response->json('message')
                ?? $response->json('data.message')
                ?? $response->body()
                ?? 'Unknown Flutterwave API error.');

            return [
                'success' => false,
                'message' => 'Failed to initialize Flutterwave checkout: ' . $errorMessage,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to initialize Flutterwave checkout: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a completed transaction by its Flutterwave transaction id.
     * Returns the transaction status/amount/currency/tx_ref and, when the
     * charge was made by card, the reusable card token for auto-renewal.
     */
    public function verifyTransaction(array $credentials, string $transactionId): array
    {
        if (empty($credentials['secret_key'])) {
            return ['success' => false, 'message' => 'Missing required Flutterwave credential: secret_key'];
        }

        $baseUrl = rtrim((string) ($credentials['base_url'] ?? 'https://api.flutterwave.com'), '/');
        $secretKey = (string) $credentials['secret_key'];
        $endpoint = $baseUrl . '/v3/transactions/' . $transactionId . '/verify';

        try {
            $response = Http::timeout(15)
                ->retry(1, 250)
                ->withToken($secretKey)
                ->acceptJson()
                ->get($endpoint);

            if (!$response->successful() || strtolower((string) $response->json('status')) !== 'success') {
                $errorMessage = (string) ($response->json('message') ?? $response->body() ?? 'Unknown Flutterwave API error.');

                return ['success' => false, 'message' => 'Failed to verify transaction: ' . $errorMessage];
            }

            $data = (array) $response->json('data', []);

            return [
                'success' => true,
                'status' => strtolower((string) ($data['status'] ?? '')),
                'amount' => (float) ($data['amount'] ?? 0),
                'currency' => strtoupper((string) ($data['currency'] ?? '')),
                'tx_ref' => (string) ($data['tx_ref'] ?? ''),
                'flw_ref' => (string) ($data['flw_ref'] ?? ''),
                'payment_type' => (string) ($data['payment_type'] ?? ''),
                'customer_email' => (string) ($data['customer']['email'] ?? ''),
                'card_token' => $data['card']['token'] ?? null,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Failed to verify transaction: ' . $e->getMessage()];
        }
    }

    /**
     * Charge a previously-saved card token, for auto-renewal.
     */
    public function chargeToken(array $credentials, string $token, string $email, float $amount, string $currency, string $txRef): array
    {
        if (empty($credentials['secret_key'])) {
            return ['success' => false, 'message' => 'Missing required Flutterwave credential: secret_key'];
        }

        $baseUrl = rtrim((string) ($credentials['base_url'] ?? 'https://api.flutterwave.com'), '/');
        $secretKey = (string) $credentials['secret_key'];
        $endpoint = $baseUrl . '/v3/tokenized-charges';

        try {
            $response = Http::timeout(20)
                ->retry(1, 250)
                ->withToken($secretKey)
                ->acceptJson()
                ->post($endpoint, [
                    'token' => $token,
                    'currency' => strtoupper($currency),
                    'amount' => $amount,
                    'email' => $email,
                    'tx_ref' => $txRef,
                ]);

            $status = strtolower((string) $response->json('status'));
            $chargeStatus = strtolower((string) $response->json('data.status'));

            if ($response->successful() && $status === 'success' && in_array($chargeStatus, ['successful', 'success'], true)) {
                return [
                    'success' => true,
                    'transaction_id' => (string) ($response->json('data.id') ?? ''),
                    'tx_ref' => $txRef,
                ];
            }

            $errorMessage = (string) ($response->json('message') ?? $response->json('data.processor_response') ?? 'Unknown Flutterwave API error.');

            return ['success' => false, 'message' => 'Auto-renewal charge failed: ' . $errorMessage];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Auto-renewal charge failed: ' . $e->getMessage()];
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
