<?php

namespace App\Services\Connectors\Contracts;

interface IntegrationConnectorInterface
{
    public function test(array $credentials): array;
}
