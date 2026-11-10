<?php

namespace Tests\Feature;

use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorSubscription;
use App\Models\User;
use App\Support\AssistantKnowledge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['assistant.enabled' => true, 'assistant.base_url' => 'https://assistant.test', 'assistant.api_key' => 'server-only-assistant-key']);
        Http::preventStrayRequests();
    }

    public function test_guests_and_deactivated_users_cannot_use_assistant(): void
    {
        $this->postJson('/assistant/chat', ['question' => 'How does billing work?', 'consent' => true])->assertUnauthorized();
        $this->actingAs(User::factory()->create(['is_active' => false]))
            ->postJson('/assistant/chat', ['question' => 'How does billing work?', 'consent' => true])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_validation_requires_consent_and_a_bounded_question(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([['question' => 'Help'], ['question' => 'Help', 'consent' => false], ['question' => '   ', 'consent' => true], ['question' => str_repeat('a', 1201), 'consent' => true], ['question' => ['bad'], 'consent' => true]] as $input) {
            $this->postJson('/assistant/chat', $input)->assertUnprocessable();
        }
        Http::assertNothingSent();
    }

    public function test_only_approved_docs_question_and_pseudonymous_identity_are_forwarded(): void
    {
        $user = User::factory()->create(['name' => 'Private Owner Name', 'email' => 'private-owner@example.com']);
        Http::fake(['https://assistant.test/v1/chat' => Http::response(['answer' => 'Open Billing to view your trial.', 'sources' => [
            ['title' => 'Untrusted title', 'path' => '/user-guide#paying-garagehq-subscriptions-and-platform-invoices'],
            ['title' => 'Bad link', 'path' => 'https://attacker.test'], ['title' => 'Admin', 'path' => '/user-guide#platform-administration'],
        ]])]);
        $response = $this->actingAs($user)->postJson('/assistant/chat', ['question' => 'How does my trial work?', 'consent' => true,
            'actor' => 'user:someone-else', 'role' => 'platform_admin', 'docs' => ['secret' => 'untrusted context'], 'history' => [['text' => 'old message']]])
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')->assertJsonPath('answer', 'Open Billing to view your trial.')
            ->assertJsonCount(1, 'sources')->assertJsonPath('sources.0.title', 'Paying GarageHQ subscriptions and platform invoices');
        Http::assertSent(function (Request $request) use ($user) {
            $data = $request->data();
            $this->assertSame(['system', 'actor', 'role', 'question', 'docs'], array_keys($data));
            $this->assertSame('garagehq', $data['system']);
            $this->assertSame('garage_staff', $data['role']);
            $this->assertMatchesRegularExpression('/^user:[a-f0-9]{64}$/', $data['actor']);
            $body = json_encode($data);
            foreach ([$user->email, $user->name, 'untrusted context', 'old message', 'platform-administration'] as $privateValue) {
                $this->assertStringNotContainsString($privateValue, $body);
            }

            return $request->hasHeader('Authorization', 'Bearer server-only-assistant-key') && strlen($body) < 220000;
        });
        $this->assertStringNotContainsString('server-only-assistant-key', $response->getContent());
    }

    public function test_supplier_knowledge_excludes_garage_and_platform_chapters(): void
    {
        $vendor = Vendor::create(['name' => 'Docs supplier', 'email' => 'supplier-help@example.com', 'vendor_type' => 'supplier']);
        $user = User::factory()->create(['vendor_id' => $vendor->id]);
        $user->assignRole(Role::findOrCreate('supplier_admin', 'web'));
        $knowledge = app(AssistantKnowledge::class);
        $content = json_encode($knowledge->documents($user));
        $this->assertSame('supplier_admin', $knowledge->role($user));
        $this->assertStringContainsString('Supplier catalog and listings', $content);
        $this->assertStringNotContainsString('Repair work orders and intake', $content);
        $this->assertStringNotContainsString('user-guide#platform-administration', $content);
        $this->assertStringContainsString('Platform administration', json_encode($knowledge->documents(User::factory()->create(['is_platform_user' => true]))));
    }

    public function test_provider_errors_are_safe_and_missing_config_makes_no_request(): void
    {
        $this->actingAs(User::factory()->create());
        $sequence = Http::sequence()->push(['error' => 'private-upstream-secret'], 401)->push(['error' => 'private-upstream-secret'], 500)
            ->push(['error' => 'private-upstream-secret'], 502)->push(['answer' => []]);
        Http::fake(['*' => $sequence]);
        foreach ([401, 500, 502] as $status) {
            $this->postJson('/assistant/chat', ['question' => 'Explain Billing', 'consent' => true])
                ->assertServiceUnavailable()->assertDontSee('private-upstream-secret');
        }
        $this->postJson('/assistant/chat', ['question' => 'Explain Billing', 'consent' => true])->assertServiceUnavailable();
        config(['assistant.api_key' => null]);
        Http::fake();
        $this->postJson('/assistant/chat', ['question' => 'Explain Billing', 'consent' => true])->assertServiceUnavailable();
        Http::assertNothingSent();
    }

    public function test_provider_timeout_and_rate_limit_are_handled(): void
    {
        $this->actingAs(User::factory()->create());
        Http::fake(['*' => Http::sequence()->pushFailedConnection()->push(['error' => 'limited'], 429)]);
        $this->postJson('/assistant/chat', ['question' => 'Explain Billing', 'consent' => true])->assertServiceUnavailable();
        $this->postJson('/assistant/chat', ['question' => 'Explain Billing', 'consent' => true])->assertTooManyRequests()->assertHeader('Retry-After', '60');
    }

    public function test_rate_limit_stops_excess_upstream_calls(): void
    {
        Http::fake(['*' => Http::response(['answer' => 'Read the guide.', 'sources' => []])]);
        $this->actingAs(User::factory()->create());
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/assistant/chat', ['question' => 'Explain Billing', 'consent' => true])->assertOk();
        }
        $this->postJson('/assistant/chat', ['question' => 'Explain Billing', 'consent' => true])->assertTooManyRequests();
        Http::assertSentCount(6);
    }

    public function test_help_works_during_expiry_and_forced_password_change(): void
    {
        $vendor = Vendor::create(['name' => 'Expired docs garage', 'email' => 'expired-ai@example.com']);
        $user = User::factory()->create(['vendor_id' => $vendor->id, 'must_change_password' => true]);
        VendorSubscription::create(['vendor_id' => $vendor->id, 'pricing_plan_id' => PricingPlan::where('audience', 'garage')->first()->id,
            'self_service' => true, 'status' => 'expired']);
        Http::fake(['*' => Http::response(['answer' => 'Open Billing to renew.'])]);
        $this->actingAs($user)->postJson('/assistant/chat', ['question' => 'How do I renew?', 'consent' => true])->assertOk();
    }

    public function test_widget_is_only_shown_to_signed_in_active_users_when_configured(): void
    {
        $this->get('/user-guide')->assertOk()->assertDontSee('data-assistant', false);
        $this->actingAs(User::factory()->create())->get('/user-guide')->assertOk()->assertSee('data-assistant', false)
            ->assertSee('Ollama Cloud')->assertDontSee('server-only-assistant-key');
        config(['assistant.enabled' => false]);
        $this->get('/user-guide')->assertOk()->assertDontSee('data-assistant', false);
    }

    public function test_export_contains_current_guide_chapters_without_credentials(): void
    {
        $path = storage_path('app/private/assistant-knowledge.json');
        try {
            $this->artisan('assistant:export-knowledge')->assertSuccessful();
            $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
            $this->assertGreaterThanOrEqual(28, count($data['guides']));
            $this->assertStringContainsString('platform-administration', json_encode($data));
            $this->assertStringNotContainsString('server-only-assistant-key', json_encode($data));
        } finally {
            File::delete($path);
        }
    }
}
