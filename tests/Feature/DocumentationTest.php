<?php

namespace Tests\Feature;

use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Models\VendorSubscription;
use App\Models\User;
use App\Support\UserGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_is_public_and_contains_current_workflows(): void
    {
        $this->get('/user-guide')->assertOk()->assertSee('Everyday work,')->assertSee('Repair work orders and intake')
            ->assertSee('Supplier plans, renewal and expiry')->assertSee('Platform administration')
            ->assertSee('does not yet provide a complete shipping/dispatch workflow')->assertSee('Swagger UI API reference');
    }

    public function test_audience_filter_preserves_common_help_and_excludes_other_workflows(): void
    {
        $this->get('/user-guide?audience=supplier')->assertOk()->assertSee('Supplier catalog and listings')
            ->assertSee('Signing in, passwords and account access')->assertDontSee('Repair work orders and intake')->assertDontSee('id="platform-administration"', false);
    }

    public function test_search_matches_chapter_content_and_has_an_empty_state(): void
    {
        $this->get('/user-guide?q=Flutterwave')->assertOk()->assertSee('Paying GarageHQ subscriptions and platform invoices')->assertDontSee('Wash orders, packages and the queue');
        $this->get('/user-guide?q=no-such-guide-topic-752684')->assertOk()->assertSee('No matching chapters');
        $this->get('/user-guide?audience=unrecognised')->assertOk()->assertSee('Repair work orders and intake');
        $this->get('/user-guide?q[]=bad&audience[]=bad')->assertOk();
    }

    public function test_search_input_is_escaped(): void
    {
        $this->get('/user-guide?q='.urlencode('<script>alert(1)</script>'))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_full_guide_can_be_downloaded(): void
    {
        $this->get('/user-guide/download')->assertOk()->assertDownload('GarageHQ-User-Guide.md');
    }

    public function test_chapter_ids_are_unique_and_audiences_are_valid(): void
    {
        $chapters = app(UserGuide::class)->chapters();
        $this->assertGreaterThanOrEqual(25, count($chapters));
        $this->assertCount(count($chapters), array_unique(array_column($chapters, 'id')));
        foreach ($chapters as $chapter) {
            foreach ($chapter['audiences'] as $audience) {
                $this->assertArrayHasKey($audience, UserGuide::AUDIENCES);
            }
        }
    }

    public function test_local_guide_links_resolve_to_registered_get_routes(): void
    {
        preg_match_all('/\]\((\/[^)]+)\)/', File::get(base_path('docs/USER_GUIDE.md')), $matches);
        foreach (array_unique($matches[1]) as $path) {
            $matched = Route::getRoutes()->match(\Illuminate\Http\Request::create($path, 'GET'));
            $this->assertContains('GET', $matched->methods(), 'Broken guide link: '.$path);
        }
    }

    public function test_api_page_loads_local_swagger_bundle_and_all_service_links(): void
    {
        $this->get('/api-docs')->assertOk()->assertSee('swagger-ui')->assertSee('/api-docs/spec/garagehq')
            ->assertSee('/api-docs/spec/cream-theme')->assertSee('/api-docs/spec/onepass');
    }

    public function test_specs_are_json_with_unique_operations_and_declared_security(): void
    {
        foreach (['garagehq', 'cream-theme', 'onepass', 'assistant'] as $service) {
            $response = $this->get('/api-docs/spec/'.$service)->assertOk()->assertHeader('Content-Type', 'application/json');
            $spec = $response->json();
            $this->assertSame('3.0.3', $spec['openapi']);
            $ids = [];
            foreach ($spec['paths'] as $path => $operations) {
                foreach ($operations as $method => $operation) {
                    $ids[] = $operation['operationId'];
                    $this->assertNotEmpty($operation['responses']);
                    foreach ($operation['security'] ?? $spec['security'] as $requirement) {
                        foreach (array_keys($requirement) as $scheme) {
                            $this->assertArrayHasKey($scheme, $spec['components']['securitySchemes']);
                        }
                    }
                    if ($service === 'garagehq') {
                        $route = Route::getRoutes()->match(\Illuminate\Http\Request::create($path, strtoupper($method)));
                        $this->assertContains(strtoupper($method), $route->methods());
                    }
                }
            }
            $this->assertCount(count($ids), array_unique($ids));
        }
        $this->get('/api-docs/spec/not-a-service')->assertNotFound();
    }

    public function test_help_remains_available_with_expired_subscription_and_forced_password_change(): void
    {
        $vendor = Vendor::create(['name' => 'Expired garage', 'email' => 'expired-docs@example.com']);
        $user = User::factory()->create(['vendor_id' => $vendor->id, 'must_change_password' => true]);
        VendorSubscription::create(['vendor_id' => $vendor->id, 'pricing_plan_id' => PricingPlan::where('audience', 'garage')->first()->id,
            'self_service' => true, 'status' => 'expired']);
        $this->actingAs($user)->get('/user-guide')->assertOk();
        $this->get('/user-guide/download')->assertOk();
        $this->get('/api-docs')->assertOk();
        $this->get('/api-docs/spec/onepass')->assertOk();
    }
}
