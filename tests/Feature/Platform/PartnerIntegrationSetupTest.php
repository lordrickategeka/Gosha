<?php

namespace Tests\Feature\Platform;

use App\Domains\Platform\Livewire\Platform\ApiIntegrationsComponent;
use App\Domains\Platform\Models\ApiIntegration;
use App\Domains\Platform\Services\ApiIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerIntegrationSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_setup_fields_render_and_credentials_are_encrypted(): void
    {
        Http::preventStrayRequests();
        Livewire::test(ApiIntegrationsComponent::class)->assertSee('Partner API key')->assertSee('Partner API secret')
            ->set('credentials', ['base_url' => 'https://cream.test', 'api_key' => 'cream-key', 'api_secret' => 'cream-secret', 'environment' => 'test'])
            ->set('isActive', true)->call('save')->assertHasNoErrors()
            ->call('selectProvider', 'onepass')->assertSee('GarageHQ partner key')
            ->set('credentials', ['base_url' => 'https://onepass.test', 'api_key' => 'onepass-secret'])
            ->set('isActive', true)->call('save')->assertHasNoErrors();
        $stored = DB::table('api_integrations')->pluck('credentials')->implode('');
        $this->assertStringNotContainsString('cream-secret', $stored);
        $this->assertStringNotContainsString('onepass-secret', $stored);
        $this->assertSame('cream-secret', ApiIntegration::where('provider', 'cream_theme')->firstOrFail()->credentials['api_secret']);
        Http::assertNothingSent();
    }

    public function test_invalid_payment_environment_is_rejected(): void
    {
        Livewire::test(ApiIntegrationsComponent::class)
            ->set('credentials', ['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret', 'environment' => 'anything'])
            ->call('save')->assertHasErrors('credentials.environment');
    }

    public function test_connection_checks_do_not_send_messages_or_create_payments(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://cream.test/api/partner-keys/payments' => Http::response([]),
            'https://onepass.test/health/ready' => Http::response(['status' => 'ready']),
            'https://onepass.test/auth/otp/verify' => Http::response(['code' => 'invalid_request'], 400)]);
        $service = app(ApiIntegrationService::class);
        $cream = $service->upsertIntegration('cream_theme', ['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret', 'environment' => 'test'], true);
        $otp = $service->upsertIntegration('onepass', ['base_url' => 'https://onepass.test', 'api_key' => 'key'], true);
        $this->assertTrue($service->testConnection($cream)['success']);
        $this->assertTrue($service->testConnection($otp)['success']);
        Http::assertSentCount(3);
        Http::assertNotSent(fn ($request) => $request->method() !== 'GET' && ! ($request->url() === 'https://onepass.test/auth/otp/verify' && empty($request->data())));
    }

    public function test_connection_test_reports_certificate_failure_without_exposing_upstream_secrets(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('cURL error 60: certificate chain failure private-upstream-data'));
        $result = (new \App\Domains\Platform\Services\CreamThemeConnector)->test(['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret']);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('cURL 60', $result['message']);
        $this->assertStringContainsString('INTEGRATIONS_CA_BUNDLE', $result['message']);
        $this->assertStringNotContainsString('private-upstream-data', $result['message']);
    }

    public function test_custom_ca_bundle_keeps_certificate_verification_enabled(): void
    {
        config(['integrations.ca_bundle' => __FILE__]);
        // Fake transport: inspect options without using this fixture as a real certificate.
        Http::fake(function ($request, $options) {
            $this->assertSame(__FILE__, $options['verify']);

            return Http::response([]);
        });
        $result = (new \App\Domains\Platform\Services\CreamThemeConnector)->test(['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret']);
        $this->assertTrue($result['success']);
    }

    public function test_default_ca_configuration_also_keeps_verification_enabled(): void
    {
        config(['integrations.ca_bundle' => null]);
        Http::fake(function ($request, $options) {
            $this->assertTrue($options['verify']);

            return Http::response([]);
        });
        $result = (new \App\Domains\Platform\Services\CreamThemeConnector)->test(['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret']);
        $this->assertTrue($result['success']);
    }

    public function test_missing_ca_bundle_reports_configuration_error(): void
    {
        config(['integrations.ca_bundle' => base_path('missing-ca-file.pem')]);
        Http::preventStrayRequests();
        $result = (new \App\Domains\Platform\Services\CreamThemeConnector)->test(['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret']);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('cannot be read', $result['message']);
        Http::assertNothingSent();
    }

    public function test_html_login_page_is_not_treated_as_a_healthy_api(): void
    {
        Http::fake(['*' => Http::response('<html>Sign in</html>', 200, ['Content-Type' => 'text/html'])]);
        $cream = (new \App\Domains\Platform\Services\CreamThemeConnector)->test(['base_url' => 'https://cream.test', 'api_key' => 'key', 'api_secret' => 'secret']);
        $otp = (new \App\Domains\Platform\Services\OnePassConnector)->test(['base_url' => 'https://onepass.test', 'api_key' => 'key']);
        $this->assertFalse($cream['success']);
        $this->assertFalse($otp['success']);
    }

    public function test_onepass_rejects_an_invalid_key_even_if_readiness_passes(): void
    {
        Http::fake(['https://onepass.test/health/ready' => Http::response(['status' => 'ready']),
            'https://onepass.test/auth/otp/verify' => Http::response(['code' => 'unauthorized'], 401)]);
        $result = (new \App\Domains\Platform\Services\OnePassConnector)->test(['base_url' => 'https://onepass.test', 'api_key' => 'invalid']);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('HTTP 401', $result['message']);
    }
}
