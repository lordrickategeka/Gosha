<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Services\Contracts\IntegrationConnectorInterface;
use Illuminate\Http\Client\PendingRequest;
use RuntimeException;

class OnePassConnector implements IntegrationConnectorInterface
{
    private function client(array $credentials): PendingRequest
    {
        $url = rtrim($credentials['base_url'] ?? '', '/');
        if (! filter_var($url, FILTER_VALIDATE_URL) || empty($credentials['api_key'])
            || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'])) {
            throw new RuntimeException('Configure OnePass URL and partner key.');
        }
        if (app()->environment('production') && ! str_starts_with($url, 'https://')) {
            throw new RuntimeException('OnePass requires HTTPS in production.');
        }

        return IntegrationHttpClient::make()->baseUrl($url)->withToken($credentials['api_key'])
            ->withHeaders(['X-Forwarded-For' => request()->ip()])
            ->asJson()->acceptJson()->connectTimeout(5)->timeout(15);
    }

    public function test(array $credentials): array
    {
        try {
            $response = $this->client($credentials)->get('/health/ready');
            $ok = $response->successful() && $response->json('status') === 'ready';

            if (! $ok) {
                return ['success' => false, 'message' => $response->successful()
                    ? 'OnePass returned an unexpected readiness response. Check the base URL.' : IntegrationHttpClient::rejected($response)];
            }
            // Authentication runs before input validation. An empty verification request
            // checks the key without issuing, consuming or sending any OTP.
            $probe = $this->client($credentials)->post('/auth/otp/verify', []);
            $authenticated = $probe->status() === 400 && $probe->json('code') === 'invalid_request';

            return ['success' => $authenticated, 'message' => $authenticated
                ? 'OnePass is ready and the partner key is accepted. Verify a signup to check email delivery.'
                : ($probe->successful() ? 'OnePass returned an unexpected authentication response. Check the base URL.' : IntegrationHttpClient::rejected($probe))];
        } catch (\Throwable $exception) {
            return ['success' => false, 'message' => IntegrationHttpClient::failure($exception)];
        }
    }

    public function otp(string $action, string $identifier, ?string $code = null): array
    {
        $integration = app(ApiIntegrationService::class)->getIntegration('onepass');
        if (! $integration?->isActive()) {
            return ['success' => false, 'message' => 'Verification is temporarily unavailable. Please try again later.'];
        }
        try {
            $response = $this->client($integration->credentials)->post('/auth/otp/'.$action,
                array_filter(['identifier' => $identifier, 'code' => $code], fn ($value) => $value !== null));
            $error = $response->json('code');

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'retry_after' => min(900, max(60, (int) $response->header('Retry-After'))),
                'message' => match (true) {
                    $response->successful() => $action === 'request' ? 'Verification code sent.' : 'Email verified.',
                    $response->status() === 429 => 'Please wait before requesting or checking another code.',
                    $error === 'otp_expired' => 'Your code expired. Please request a new code.',
                    $error === 'invalid_code' => 'That code is incorrect. Please try again.',
                    $error === 'too_many_attempts' => 'Too many incorrect codes. Please request a new code.',
                    default => 'Verification is temporarily unavailable. Please try again later.',
                },
            ];
        } catch (\Throwable) {
            return ['success' => false, 'message' => 'Verification is temporarily unavailable. Please try again later.'];
        }
    }
}
