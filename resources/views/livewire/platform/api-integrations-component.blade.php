<div>
    <div class="mb-6">
        <div class="badge badge-warning mb-2">Platform Admin</div>
        <h1 class="text-2xl font-bold">API Integrations</h1>
        <p class="text-base-content/60">Manage service credentials, activation status, and webhook endpoints.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card bg-base-100 shadow-sm lg:col-span-1">
            <div class="card-body">
                <h2 class="card-title text-lg">Services</h2>
                <div class="space-y-2">
                    @foreach ($providers as $providerKey => $provider)
                        @php
                            $providerIntegration = $integrations->get($providerKey);
                            $isSelected = $selectedProvider === $providerKey;
                        @endphp
                        <button
                            wire:click="selectProvider('{{ $providerKey }}')"
                            class="btn w-full justify-between {{ $isSelected ? 'btn-primary' : 'btn-ghost' }}"
                        >
                            <span>{{ $provider['label'] }}</span>
                            @if ($providerIntegration)
                                <span class="badge {{ $providerIntegration->is_active ? 'badge-success' : 'badge-neutral' }}">
                                    {{ $providerIntegration->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            @else
                                <span class="badge badge-ghost">Not Configured</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title text-lg">{{ $providers[$selectedProvider]['label'] ?? ucfirst($selectedProvider) }} Configuration</h2>
                <p class="text-sm text-base-content/60 mb-4">{{ $providers[$selectedProvider]['description'] ?? '' }}</p>

                <div class="mb-4">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" class="toggle toggle-success" wire:model="isActive" />
                        <span class="label-text">Set integration as active</span>
                    </label>
                </div>

                @if ($selectedProvider === 'whatsapp')
                    @include('livewire.platform.integrations.forms.whatsapp')
                @elseif ($selectedProvider === 'email')
                    @include('livewire.platform.integrations.forms.email')
                @elseif ($selectedProvider === 'flutterwave')
                    @include('livewire.platform.integrations.forms.flutterwave')
                @endif

                <div class="divider"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Webhook URL</span></label>
                        <input type="url" wire:model.defer="webhookUrl" class="input input-bordered w-full" placeholder="https://example.com/api/webhooks/provider" />
                        @error('webhookUrl')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Webhook Secret</span></label>
                        <input type="text" wire:model.defer="webhookSecret" class="input input-bordered w-full" placeholder="Webhook signing secret" />
                        @error('webhookSecret')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>

                @if ($selectedProvider === 'whatsapp' && $selectedIntegration)
                    <div class="divider">Send Test Message</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Recipient Phone (E.164)</span></label>
                            <input type="text" wire:model.defer="testRecipientPhone" class="input input-bordered w-full" placeholder="2567XXXXXXXX" />
                            @error('testRecipientPhone')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-control md:col-span-2">
                            <label class="label"><span class="label-text">Message Body</span></label>
                            <textarea wire:model.defer="testMessageBody" class="textarea textarea-bordered w-full" rows="3" placeholder="Type a short test message"></textarea>
                            @error('testMessageBody')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>
                @endif

                @if ($selectedProvider === 'flutterwave' && $selectedIntegration)
                    <div class="divider">Initiate Test Payment</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Customer Email</span></label>
                            <input type="email" wire:model.defer="flutterwaveTestCustomerEmail" class="input input-bordered w-full" placeholder="customer@example.com" />
                            @error('flutterwaveTestCustomerEmail')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Customer Name</span></label>
                            <input type="text" wire:model.defer="flutterwaveTestCustomerName" class="input input-bordered w-full" placeholder="Customer Name" />
                            @error('flutterwaveTestCustomerName')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Amount</span></label>
                            <input type="number" step="0.01" min="100" wire:model.defer="flutterwaveTestAmount" class="input input-bordered w-full" placeholder="1000" />
                            @error('flutterwaveTestAmount')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Currency</span></label>
                            <input type="text" maxlength="3" wire:model.defer="flutterwaveTestCurrency" class="input input-bordered w-full" placeholder="UGX" />
                            @error('flutterwaveTestCurrency')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    @if ($flutterwaveCheckoutLink)
                        <div class="alert alert-info mt-4">
                            <span>Checkout link ready:</span>
                            <a href="{{ $flutterwaveCheckoutLink }}" target="_blank" class="link link-primary break-all">{{ $flutterwaveCheckoutLink }}</a>
                        </div>
                    @endif
                @endif

                <div class="flex flex-wrap gap-2 mt-6">
                    <button class="btn btn-primary" wire:click="save">Save Integration</button>

                    @if ($selectedIntegration)
                        <button class="btn btn-outline" wire:click="testConnection({{ $selectedIntegration->id }})">Test Connection</button>
                        @if ($selectedProvider === 'whatsapp')
                            <button class="btn btn-accent" wire:click="sendTestMessage({{ $selectedIntegration->id }})">Send Test Message</button>
                        @elseif ($selectedProvider === 'flutterwave')
                            <button class="btn btn-accent" wire:click="initiateFlutterwaveTestPayment({{ $selectedIntegration->id }})">Initiate Test Payment</button>
                        @endif
                        <button class="btn btn-secondary" wire:click="toggleStatus({{ $selectedIntegration->id }})">
                            {{ $selectedIntegration->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button class="btn btn-error btn-outline" wire:click="deleteIntegration({{ $selectedIntegration->id }})"
                            wire:confirm="Remove this integration?">
                            Remove
                        </button>
                    @endif
                </div>

                @if ($selectedIntegration)
                    <div class="text-xs text-base-content/60 mt-4">
                        Last tested:
                        {{ $selectedIntegration->last_tested_at ? $selectedIntegration->last_tested_at->diffForHumans() : 'Never' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
            <h2 class="card-title text-lg">Recent Logs</h2>
            @if ($recentLogs->isEmpty())
                <p class="text-base-content/60 text-sm">No activity logs for this provider yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentLogs as $log)
                                <tr>
                                    <td class="text-xs">{{ $log->created_at->format('d M Y H:i') }}</td>
                                    <td><span class="badge badge-ghost badge-sm">{{ $log->action }}</span></td>
                                    <td>
                                        <span class="badge badge-sm {{ $log->status === 'success' ? 'badge-success' : 'badge-error' }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td class="text-xs">{{ $log->error_message ?: 'OK' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
