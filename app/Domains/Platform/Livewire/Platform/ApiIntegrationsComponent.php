<?php

namespace App\Domains\Platform\Livewire\Platform;

use App\Domains\Platform\Models\ApiIntegration;
use App\Domains\Platform\Services\ApiIntegrationService;
use Livewire\Component;

class ApiIntegrationsComponent extends Component
{
    public string $selectedProvider = 'whatsapp';
    public array $credentials = [];
    public bool $isActive = false;
    public ?string $webhookUrl = null;
    public ?string $webhookSecret = null;
    public string $testRecipientPhone = '';
    public string $testMessageBody = 'Hello from GarageHQ WhatsApp integration test.';
    public string $flutterwaveTestCustomerEmail = '';
    public string $flutterwaveTestCustomerName = 'GarageHQ Test Customer';
    public float $flutterwaveTestAmount = 1000;
    public string $flutterwaveTestCurrency = 'UGX';
    public ?string $flutterwaveCheckoutLink = null;

    public function mount(): void
    {
        $providers = array_keys($this->providers());

        if (!empty($providers)) {
            $this->selectedProvider = $providers[0];
            $this->hydrateFormForProvider($this->selectedProvider);
        }
    }

    public function selectProvider(string $provider): void
    {
        if (!array_key_exists($provider, $this->providers())) {
            return;
        }

        $this->selectedProvider = $provider;
        $this->hydrateFormForProvider($provider);
    }

    public function save(): void
    {
        $this->validate($this->rules(), [], $this->validationAttributes());

        $service = app(ApiIntegrationService::class);

        $service->upsertIntegration(
            provider: $this->selectedProvider,
            credentials: $this->credentials,
            isActive: $this->isActive,
            webhookUrl: $this->webhookUrl,
            webhookSecret: $this->webhookSecret
        );

        session()->flash('success', ucfirst($this->selectedProvider) . ' integration saved successfully.');
    }

    public function toggleStatus(int $integrationId): void
    {
        $integration = ApiIntegration::query()->findOrFail($integrationId);
        app(ApiIntegrationService::class)->toggleStatus($integration);

        if ($integration->provider === $this->selectedProvider) {
            $this->hydrateFormForProvider($integration->provider);
        }

        session()->flash('success', $integration->providerLabel() . ' status updated.');
    }

    public function testConnection(int $integrationId): void
    {
        $integration = ApiIntegration::query()->findOrFail($integrationId);
        $result = app(ApiIntegrationService::class)->testConnection($integration);

        if ($integration->provider === $this->selectedProvider) {
            $this->hydrateFormForProvider($integration->provider);
        }

        if ($result['success']) {
            session()->flash('success', $result['message']);
            return;
        }

        session()->flash('error', $result['message']);
    }

    public function sendTestMessage(int $integrationId): void
    {
        $integration = ApiIntegration::query()->findOrFail($integrationId);

        if ($integration->provider !== 'whatsapp') {
            session()->flash('error', 'Test message is only available for WhatsApp integrations.');
            return;
        }

        $this->validate([
            'testRecipientPhone' => 'required|string|max:30',
            'testMessageBody' => 'required|string|max:1024',
        ]);

        /** @var ApiIntegrationService $service */
        $service = app(ApiIntegrationService::class);
        $result = $service->sendTestMessage(
            $integration,
            $this->testRecipientPhone,
            $this->testMessageBody
        );

        if ($result['success']) {
            session()->flash('success', $result['message']);
            return;
        }

        session()->flash('error', $result['message']);
    }

    public function initiateFlutterwaveTestPayment(int $integrationId): void
    {
        $integration = ApiIntegration::query()->findOrFail($integrationId);

        if ($integration->provider !== 'flutterwave') {
            session()->flash('error', 'Test payment is only available for Flutterwave integrations.');
            return;
        }

        $this->validate([
            'flutterwaveTestCustomerEmail' => 'required|email|max:255',
            'flutterwaveTestCustomerName' => 'required|string|max:255',
            'flutterwaveTestAmount' => 'required|numeric|min:100',
            'flutterwaveTestCurrency' => 'required|string|size:3',
        ]);

        /** @var ApiIntegrationService $service */
        $service = app(ApiIntegrationService::class);

        $result = $service->initiateFlutterwaveTestPayment(
            integration: $integration,
            customerEmail: $this->flutterwaveTestCustomerEmail,
            customerName: $this->flutterwaveTestCustomerName,
            amount: (float) $this->flutterwaveTestAmount,
            currency: strtoupper($this->flutterwaveTestCurrency),
            redirectUrl: url('/platform/integrations')
        );

        if ($result['success']) {
            $this->flutterwaveCheckoutLink = $result['checkout_link'] ?? null;
            session()->flash('success', $result['message']);
            return;
        }

        $this->flutterwaveCheckoutLink = null;
        session()->flash('error', $result['message']);
    }

    public function deleteIntegration(int $integrationId): void
    {
        $integration = ApiIntegration::query()->findOrFail($integrationId);
        $provider = $integration->provider;
        $label = $integration->providerLabel();

        $integration->delete();

        if ($provider === $this->selectedProvider) {
            $this->credentials = [];
            $this->isActive = false;
            $this->webhookUrl = null;
            $this->webhookSecret = null;
        }

        session()->flash('success', $label . ' integration removed.');
    }

    public function render()
    {
        $integrations = ApiIntegration::query()->orderBy('provider')->get()->keyBy('provider');
        $selectedIntegration = $integrations->get($this->selectedProvider);

        $recentLogs = $selectedIntegration
            ? $selectedIntegration->logs()->latest()->limit(10)->get()
            : collect();

        return view('livewire.platform.api-integrations-component', [
            'providers' => $this->providers(),
            'integrations' => $integrations,
            'selectedIntegration' => $selectedIntegration,
            'recentLogs' => $recentLogs,
        ]);
    }

    private function hydrateFormForProvider(string $provider): void
    {
        $integration = ApiIntegration::query()->where('provider', $provider)->first();
        $this->flutterwaveCheckoutLink = null;

        if (!$integration) {
            $this->credentials = [];
            $this->isActive = false;
            $this->webhookUrl = url('/api/webhooks/' . $provider);
            $this->webhookSecret = null;
            return;
        }

        $this->credentials = $integration->credentials;
        $this->isActive = (bool) $integration->is_active;
        $this->webhookUrl = $integration->webhook_url ?: url('/api/webhooks/' . $provider);
        $this->webhookSecret = $integration->webhook_secret;
    }

    protected function rules(): array
    {
        $rules = [
            'selectedProvider' => 'required|string',
            'isActive' => 'boolean',
            'webhookUrl' => 'nullable|url|max:255',
            'webhookSecret' => 'nullable|string|max:255',
        ];

        foreach ($this->providerFields($this->selectedProvider) as $field) {
            $key = $field['key'];
            $required = !empty($field['required']);
            $type = $field['type'] ?? 'text';

            $fieldRules = $required ? ['required'] : ['nullable'];

            if ($type === 'number') {
                $fieldRules[] = 'numeric';
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:255';
            }

            $rules['credentials.' . $key] = implode('|', $fieldRules);
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->providerFields($this->selectedProvider) as $field) {
            $attributes['credentials.' . $field['key']] = strtolower($field['label'] ?? $field['key']);
        }

        return $attributes;
    }

    private function providers(): array
    {
        return config('integrations.providers', []);
    }

    private function providerFields(string $provider): array
    {
        return $this->providers()[$provider]['fields'] ?? [];
    }
}
