<?php

namespace Tests\Feature\Auth;

use App\Domains\HR\Livewire\Auth\VendorRegistration;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Services\ApiIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SignupVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        app(ApiIntegrationService::class)->upsertIntegration('onepass', [
            'base_url' => 'https://onepass.test', 'api_key' => 'garage-partner-key',
        ], true);
    }

    private function wizard()
    {
        return Livewire::test(VendorRegistration::class)->set('vendor_name', 'Garage')->set('vendor_email', 'owner@example.com')
            ->call('nextStep')->set('owner_name', 'Owner')->set('password', 'a-long-password')->set('password_confirmation', 'a-long-password');
    }

    public function test_signup_cannot_advance_or_create_a_garage_without_email_verification(): void
    {
        $this->wizard()->call('nextStep')->assertHasErrors('verification')->assertSet('currentStep', 2)
            ->call('register')->assertHasErrors('verification')->assertSet('currentStep', 2);
        $this->assertSame(0, Vendor::count());
        Http::assertNothingSent();
    }

    public function test_email_change_invalidates_previous_proof(): void
    {
        Http::fake(['https://onepass.test/*' => Http::response(['message' => 'OK'])]);
        $this->wizard()->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')
            ->call('nextStep')->assertSet('currentStep', 3)
            ->call('goToStep', 2)->set('useBusinessEmail', false)->set('owner_email', 'changed@example.com')
            ->call('nextStep')->assertSet('currentStep', 2)->assertHasErrors('verification');
    }

    public function test_incorrect_code_does_not_grant_verification(): void
    {
        Http::fake(['https://onepass.test/*' => Http::sequence()->push(['message' => 'OTP dispatched'])
            ->push(['code' => 'invalid_code'], 401)]);
        $this->wizard()->call('sendVerificationCode')->set('verificationCode', '000000')->call('verifyEmail')
            ->assertHasErrors('verification')->assertSee('That code is incorrect')
            ->call('nextStep')->assertHasErrors('verification')->assertSet('currentStep', 2);
    }

    public function test_code_requires_a_matching_challenge_and_cannot_be_replayed_in_another_signup(): void
    {
        Http::fake(['https://onepass.test/*' => Http::response(['message' => 'OK'])]);
        $first = $this->wizard()->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail');
        $this->wizard()->set('verificationCode', '123456')->call('verifyEmail')->assertHasErrors('verification');
        $first->call('nextStep')->assertSet('currentStep', 3);
        Http::assertSentCount(2);
    }

    public function test_proof_expires_before_registration_and_requires_reverification(): void
    {
        Http::fake(['https://onepass.test/*' => Http::response(['message' => 'OK'])]);
        $wizard = $this->wizard()->call('sendVerificationCode')->set('verificationCode', '123456')->call('verifyEmail')
            ->call('nextStep')->call('nextStep')->assertSet('currentStep', 4);
        $this->travel(31)->minutes();
        $wizard->call('register')->assertSet('currentStep', 2)->assertHasErrors('verification');
        $this->assertSame(0, Vendor::count());
    }

    public function test_resend_obeys_provider_retry_after_without_blind_retries(): void
    {
        Http::fake(['https://onepass.test/*' => Http::response(['code' => 'rate_limited'], 429, ['Retry-After' => 300])]);
        $wizard = $this->wizard()->call('sendVerificationCode')->assertHasErrors('verification');
        $this->travel(2)->minutes();
        $wizard->call('sendVerificationCode')->assertHasErrors('verification');
        Http::assertSentCount(1);
    }

    public function test_optional_phone_verification_uses_same_service_and_is_not_required_for_email_signup(): void
    {
        app(ApiIntegrationService::class)->getIntegration('onepass')->update(['credentials' => [
            'base_url' => 'https://onepass.test', 'api_key' => 'garage-partner-key', 'sms_enabled' => 'true',
        ]]);
        Http::fake(['https://onepass.test/*' => Http::response(['message' => 'OK'])]);
        $this->wizard()->set('owner_phone', '+256700000000')->call('sendPhoneCode')->set('phoneCode', '123456')->call('verifyPhone')
            ->assertSee('Phone verified')->call('nextStep')->assertHasErrors('verification');
        Http::assertSent(fn ($request) => $request['identifier'] === '+256700000000' && $request->hasHeader('Authorization', 'Bearer garage-partner-key'));
    }

    public function test_unconfigured_verification_service_fails_without_creating_records(): void
    {
        app(ApiIntegrationService::class)->getIntegration('onepass')->update(['is_active' => false]);
        $this->wizard()->call('sendVerificationCode')->assertHasErrors('verification')->assertSee('temporarily unavailable');
        Http::assertNothingSent();
        $this->assertSame(0, Vendor::count());
    }
}
