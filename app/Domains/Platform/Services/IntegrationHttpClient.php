<?php

namespace App\Domains\Platform\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class IntegrationHttpClient
{
    public static function make(): PendingRequest
    {
        $bundle = config('integrations.ca_bundle');
        if ($bundle && (! is_string($bundle) || ! is_file($bundle) || ! is_readable($bundle))) {
            throw new RuntimeException('The configured integration CA certificate file cannot be read. Check INTEGRATIONS_CA_BUNDLE.');
        }

        // Keep TLS verification enabled, including when Apache lacks a system CA bundle.
        return Http::withOptions(['verify' => $bundle ?: true]);
    }

    public static function failure(Throwable $exception): string
    {
        preg_match('/cURL error (\d+)/', $exception->getMessage(), $match);

        return match ((int) ($match[1] ?? 0)) {
            6 => 'The API hostname could not be resolved. Check its base URL and DNS.',
            7 => 'Could not connect to the API host. Check its port, firewall and service availability.',
            28 => 'The API connection timed out. Check service availability and network access.',
            35 => 'The TLS connection failed. Check the API server certificate and TLS configuration.',
            60 => 'HTTPS certificate verification failed (cURL 60). Configure a trusted INTEGRATIONS_CA_BUNDLE or correct the PHP CA settings.',
            77 => 'PHP could not read the CA certificate bundle (cURL 77). Check the configured file and permissions.',
            default => str_contains($exception->getMessage(), 'INTEGRATIONS_CA_BUNDLE')
                ? 'The configured CA certificate file cannot be read. Check INTEGRATIONS_CA_BUNDLE.'
                : 'The API request failed. Check its base URL, credentials and server configuration.',
        };
    }

    public static function rejected(Response $response): string
    {
        return 'API returned HTTP '.$response->status().'. '.match ($response->status()) {
            401 => 'The partner credentials were rejected.',
            403 => 'The partner is inactive, expired, or does not have access to this module.',
            404 => 'The endpoint was not found. Use the service root URL without an API endpoint.',
            429 => 'The API rate limit or partner quota was reached. Try again later.',
            502, 503, 504 => 'The service or its upstream provider is unavailable.',
            default => 'Check the service configuration and availability.',
        };
    }
}
