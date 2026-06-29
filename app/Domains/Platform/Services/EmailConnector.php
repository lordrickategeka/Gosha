<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Services\Contracts\IntegrationConnectorInterface;

class EmailConnector implements IntegrationConnectorInterface
{
    public function test(array $credentials): array
    {
        $required = ['host', 'port', 'username', 'password'];

        foreach ($required as $key) {
            if (empty($credentials[$key])) {
                return [
                    'success' => false,
                    'message' => 'Missing required Email credential: ' . $key,
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Email SMTP credentials are configured and ready for use.',
        ];
    }
}
