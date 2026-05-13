<?php

namespace Tests\Unit\Services\Connectors;

use App\Services\Connectors\WhatsAppConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppConnectorTest extends TestCase
{
    public function test_it_verifies_valid_whatsapp_credentials(): void
    {
        Http::fake([
            'https://graph.facebook.com/v22.0/123456*' => Http::response([
                'id' => '123456',
                'display_phone_number' => '+256700000000',
                'verified_name' => 'Garage HQ',
            ], 200),
        ]);

        $connector = new WhatsAppConnector();
        $result = $connector->test([
            'base_url' => 'https://graph.facebook.com',
            'api_version' => 'v22.0',
            'phone_number_id' => '123456',
            'access_token' => 'secret-token',
        ]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Garage HQ', $result['message']);
        Http::assertSentCount(1);
    }

    public function test_it_returns_helpful_message_on_auth_error(): void
    {
        Http::fake([
            'https://graph.facebook.com/v22.0/123456*' => Http::response([
                'error' => ['message' => 'Invalid OAuth access token.'],
            ], 401),
        ]);

        $connector = new WhatsAppConnector();
        $result = $connector->test([
            'base_url' => 'https://graph.facebook.com',
            'api_version' => 'v22.0',
            'phone_number_id' => '123456',
            'access_token' => 'bad-token',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('authentication failed', strtolower($result['message']));
    }

    public function test_it_sends_test_message_successfully(): void
    {
        Http::fake([
            'https://graph.facebook.com/v22.0/123456/messages' => Http::response([
                'messages' => [
                    ['id' => 'wamid.TEST123'],
                ],
            ], 200),
        ]);

        $connector = new WhatsAppConnector();

        $result = $connector->sendTestMessage([
            'base_url' => 'https://graph.facebook.com',
            'api_version' => 'v22.0',
            'phone_number_id' => '123456',
            'access_token' => 'secret-token',
        ], '256700000000', 'Hello test message');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Message ID: wamid.TEST123', $result['message']);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->url() === 'https://graph.facebook.com/v22.0/123456/messages'
                && $request->method() === 'POST'
                && ($payload['to'] ?? null) === '256700000000'
                && ($payload['type'] ?? null) === 'text';
        });
    }
}
