<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Services\Contracts\IntegrationConnectorInterface;
use Illuminate\Http\Client\PendingRequest;
use RuntimeException;

class CreamThemeConnector implements IntegrationConnectorInterface
{
    public function client(array $credentials): PendingRequest
    {
        $url = rtrim($credentials['base_url'] ?? '', '/');
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'])
            || empty($credentials['api_key']) || empty($credentials['api_secret'])) {
            throw new RuntimeException('Configure the Cream Theme base URL and partner credentials.');
        }
        if (app()->environment('production') && ! str_starts_with($url, 'https://')) {
            throw new RuntimeException('Cream Theme requires HTTPS in production.');
        }

        return IntegrationHttpClient::make()->baseUrl($url)->withHeaders([
            'X-Api-Key' => $credentials['api_key'], 'X-Api-Secret' => $credentials['api_secret'],
        ])->acceptJson()->asJson()->connectTimeout(5)->timeout(20);
    }

    public function test(array $credentials): array
    {
        try {
            // An authenticated read checks credentials without taking a payment or sending mail.
            $response = $this->client($credentials)->get('/api/partner-keys/payments');

            $valid = $response->successful() && is_array($response->json()) && ! $response->json('error');

            return ['success' => $valid, 'message' => $valid
                ? 'Cream Theme partner connection verified. Mail requires the mail module to be enabled.'
                : ($response->successful() ? 'The API returned an unexpected response. Check the Cream Theme base URL.' : IntegrationHttpClient::rejected($response))];
        } catch (\Throwable $exception) {
            return ['success' => false, 'message' => IntegrationHttpClient::failure($exception)];
        }
    }
}
