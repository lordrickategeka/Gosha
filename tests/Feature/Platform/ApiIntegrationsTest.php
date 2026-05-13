<?php

namespace Tests\Feature\Platform;

use App\Models\ApiIntegration;
use App\Services\ApiIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_credentials_are_encrypted_at_rest(): void
    {
        $integration = ApiIntegration::create([
            'provider' => 'whatsapp',
            'is_active' => true,
            'credentials' => [
                'base_url' => 'https://graph.facebook.com',
                'access_token' => 'plain-secret-token',
                'phone_number_id' => '12345',
            ],
            'webhook_url' => 'https://example.test/api/webhooks/whatsapp',
        ]);

        $rawCredentials = DB::table('api_integrations')
            ->where('id', $integration->id)
            ->value('credentials');

        $this->assertNotEmpty($rawCredentials);
        $this->assertStringNotContainsString('plain-secret-token', $rawCredentials);
        $this->assertSame('plain-secret-token', $integration->fresh()->credentials['access_token']);
    }

    public function test_service_upserts_and_logs_actions(): void
    {
        $service = app(ApiIntegrationService::class);

        $integration = $service->upsertIntegration(
            provider: 'email',
            credentials: [
                'host' => 'smtp.example.com',
                'port' => '587',
                'username' => 'mailer@example.com',
                'password' => 'top-secret',
            ],
            isActive: true,
            webhookUrl: 'https://example.test/api/webhooks/email',
            webhookSecret: 'webhook-secret'
        );

        $this->assertDatabaseHas('api_integrations', [
            'id' => $integration->id,
            'provider' => 'email',
            'is_active' => 1,
        ]);

        $result = $service->testConnection($integration->fresh());

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('api_integration_logs', [
            'api_integration_id' => $integration->id,
            'action' => 'save',
            'status' => 'success',
        ]);
        $this->assertDatabaseHas('api_integration_logs', [
            'api_integration_id' => $integration->id,
            'action' => 'test',
            'status' => 'success',
        ]);
    }

    public function test_service_sends_whatsapp_test_message_and_logs_it(): void
    {
        Http::fake([
            'https://graph.facebook.com/v22.0/123456/messages' => Http::response([
                'messages' => [
                    ['id' => 'wamid.ABC123'],
                ],
            ], 200),
        ]);

        $integration = ApiIntegration::create([
            'provider' => 'whatsapp',
            'is_active' => true,
            'credentials' => [
                'base_url' => 'https://graph.facebook.com',
                'api_version' => 'v22.0',
                'phone_number_id' => '123456',
                'access_token' => 'plain-secret-token',
            ],
            'webhook_url' => 'https://example.test/api/webhooks/whatsapp',
        ]);

        $result = app(ApiIntegrationService::class)->sendTestMessage(
            integration: $integration,
            recipientPhone: '256700000000',
            message: 'Hello from test'
        );

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('api_integration_logs', [
            'api_integration_id' => $integration->id,
            'action' => 'send_test_message',
            'status' => 'success',
        ]);
    }

    public function test_service_tests_flutterwave_connection_and_logs_it(): void
    {
        Http::fake([
            'https://api.flutterwave.com/v3/balances' => Http::response([
                'status' => 'success',
                'message' => 'Balances fetched',
                'data' => [],
            ], 200),
        ]);

        $integration = ApiIntegration::create([
            'provider' => 'flutterwave',
            'is_active' => true,
            'credentials' => [
                'base_url' => 'https://api.flutterwave.com',
                'public_key' => 'FLWPUBK_TEST-xxx',
                'secret_key' => 'FLWSECK_TEST-xxx',
            ],
            'webhook_url' => 'https://example.test/api/webhooks/flutterwave',
        ]);

        $result = app(ApiIntegrationService::class)->testConnection($integration);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('api_integration_logs', [
            'api_integration_id' => $integration->id,
            'action' => 'test',
            'status' => 'success',
        ]);
    }

    public function test_service_initiates_flutterwave_test_payment_and_logs_it(): void
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

        $integration = ApiIntegration::create([
            'provider' => 'flutterwave',
            'is_active' => true,
            'credentials' => [
                'base_url' => 'https://api.flutterwave.com',
                'public_key' => 'FLWPUBK_TEST-xxx',
                'secret_key' => 'FLWSECK_TEST-xxx',
            ],
            'webhook_url' => 'https://example.test/api/webhooks/flutterwave',
        ]);

        $result = app(ApiIntegrationService::class)->initiateFlutterwaveTestPayment(
            integration: $integration,
            customerEmail: 'tester@example.com',
            customerName: 'Test User',
            amount: 2500,
            currency: 'UGX',
            redirectUrl: 'https://example.test/platform/integrations'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('https://checkout.flutterwave.com/v3/hosted/pay/abc123', $result['checkout_link']);
        $this->assertDatabaseHas('api_integration_logs', [
            'api_integration_id' => $integration->id,
            'action' => 'initiate_test_payment',
            'status' => 'success',
        ]);
    }
}
