<?php

namespace Tests\Unit\Services\Connectors;

use App\Services\Connectors\FlutterwaveConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlutterwaveConnectorTest extends TestCase
{
    public function test_it_verifies_valid_flutterwave_credentials(): void
    {
        Http::fake([
            'https://api.flutterwave.com/v3/balances' => Http::response([
                'status' => 'success',
                'message' => 'Balances fetched',
                'data' => [],
            ], 200),
        ]);

        $connector = new FlutterwaveConnector();
        $result = $connector->test([
            'base_url' => 'https://api.flutterwave.com',
            'public_key' => 'FLWPUBK_TEST-xxx',
            'secret_key' => 'FLWSECK_TEST-xxx',
        ]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('verified', strtolower($result['message']));
        Http::assertSentCount(1);
    }

    public function test_it_returns_helpful_message_on_auth_error(): void
    {
        Http::fake([
            'https://api.flutterwave.com/v3/balances' => Http::response([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401),
        ]);

        $connector = new FlutterwaveConnector();
        $result = $connector->test([
            'base_url' => 'https://api.flutterwave.com',
            'public_key' => 'FLWPUBK_TEST-xxx',
            'secret_key' => 'bad-secret',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('authentication failed', strtolower($result['message']));
    }

    public function test_it_initiates_flutterwave_test_payment_successfully(): void
    {
        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'message' => 'Hosted Link',
                'data' => [
                    'link' => 'https://checkout.flutterwave.com/v3/hosted/pay/abc123',
                ],
            ], 200),
        ]);

        $connector = new FlutterwaveConnector();
        $result = $connector->initiateTestPayment(
            credentials: [
                'base_url' => 'https://api.flutterwave.com',
                'public_key' => 'FLWPUBK_TEST-xxx',
                'secret_key' => 'FLWSECK_TEST-xxx',
            ],
            customerEmail: 'tester@example.com',
            customerName: 'Test User',
            amount: 1500,
            currency: 'UGX',
            redirectUrl: 'https://example.test/platform/integrations',
            txRef: 'ghq_test_ref_001'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('https://checkout.flutterwave.com/v3/hosted/pay/abc123', $result['checkout_link']);
    }

    public function test_it_fails_flutterwave_test_payment_when_api_rejects_request(): void
    {
        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401),
        ]);

        $connector = new FlutterwaveConnector();
        $result = $connector->initiateTestPayment(
            credentials: [
                'base_url' => 'https://api.flutterwave.com',
                'public_key' => 'FLWPUBK_TEST-xxx',
                'secret_key' => 'bad-secret',
            ],
            customerEmail: 'tester@example.com',
            customerName: 'Test User',
            amount: 1500,
            currency: 'UGX',
            redirectUrl: 'https://example.test/platform/integrations',
            txRef: 'ghq_test_ref_002'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('failed to initialize', strtolower($result['message']));
    }
}
