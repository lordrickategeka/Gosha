<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Services\Contracts\IntegrationConnectorInterface;
use Illuminate\Support\Facades\Http;

class WhatsAppConnector implements IntegrationConnectorInterface
{
    public function test(array $credentials): array
    {
        $required = ['base_url', 'phone_number_id', 'access_token'];

        foreach ($required as $key) {
            if (empty($credentials[$key])) {
                return [
                    'success' => false,
                    'message' => 'Missing required WhatsApp credential: ' . $key,
                ];
            }
        }

        $baseUrl = rtrim((string) ($credentials['base_url'] ?? ''), '/');
        $apiVersion = (string) ($credentials['api_version'] ?? 'v22.0');
        $phoneNumberId = (string) $credentials['phone_number_id'];
        $accessToken = (string) $credentials['access_token'];
        $endpoint = "{$baseUrl}/{$apiVersion}/{$phoneNumberId}";

        try {
            $response = Http::timeout(15)
                ->retry(1, 250)
                ->withToken($accessToken)
                ->acceptJson()
                ->get($endpoint, [
                    'fields' => 'id,display_phone_number,verified_name',
                ]);

            if ($response->successful()) {
                $payload = $response->json();
                $displayPhone = (string) ($payload['display_phone_number'] ?? $phoneNumberId);
                $verifiedName = (string) ($payload['verified_name'] ?? '');

                return [
                    'success' => true,
                    'message' => $verifiedName !== ''
                        ? "WhatsApp API verified for {$verifiedName} ({$displayPhone})."
                        : "WhatsApp API verified for {$displayPhone}.",
                ];
            }

            $errorMessage = (string) ($response->json('error.message') ?? $response->body() ?? 'Unknown WhatsApp API error.');

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp authentication failed. Check access token. ' . $errorMessage,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp phone number was not found. Check phone_number_id and api_version. ' . $errorMessage,
                ];
            }

            return [
                'success' => false,
                'message' => 'WhatsApp test failed: ' . $errorMessage,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'WhatsApp test failed: ' . $e->getMessage(),
            ];
        }
    }

    public function sendTestMessage(array $credentials, string $recipientPhone, string $message): array
    {
        $required = ['base_url', 'phone_number_id', 'access_token'];

        foreach ($required as $key) {
            if (empty($credentials[$key])) {
                return [
                    'success' => false,
                    'message' => 'Missing required WhatsApp credential: ' . $key,
                ];
            }
        }

        $baseUrl = rtrim((string) ($credentials['base_url'] ?? ''), '/');
        $apiVersion = (string) ($credentials['api_version'] ?? 'v22.0');
        $phoneNumberId = (string) $credentials['phone_number_id'];
        $accessToken = (string) $credentials['access_token'];
        $endpoint = "{$baseUrl}/{$apiVersion}/{$phoneNumberId}/messages";

        try {
            $response = Http::timeout(20)
                ->retry(1, 250)
                ->withToken($accessToken)
                ->acceptJson()
                ->post($endpoint, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $recipientPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $messageId = (string) ($response->json('messages.0.id') ?? '');

                return [
                    'success' => true,
                    'message' => $messageId !== ''
                        ? "WhatsApp test message sent successfully. Message ID: {$messageId}"
                        : 'WhatsApp test message sent successfully.',
                ];
            }

            $errorMessage = (string) ($response->json('error.message') ?? $response->body() ?? 'Unknown WhatsApp API error.');

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp test message: ' . $errorMessage,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp test message: ' . $e->getMessage(),
            ];
        }
    }
}
