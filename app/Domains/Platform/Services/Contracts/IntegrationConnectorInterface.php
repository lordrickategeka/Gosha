<?php

namespace App\Domains\Platform\Services\Contracts;

interface IntegrationConnectorInterface
{
    public function test(array $credentials): array;
}
