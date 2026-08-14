<div class="gh-page">
    <div>
        <span class="gh-badge gh-badge--warning">Platform Admin</span>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:8px;">API Integrations</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage service credentials, activation status, and webhook endpoints.</p>
    </div>

    <div class="gh-split">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:12px;">Services</div>
            <div class="gh-stack" style="gap:6px;">
                @foreach ($providers as $providerKey => $provider)
                    @php
                        $providerIntegration = $integrations->get($providerKey);
                        $isSelected = $selectedProvider === $providerKey;
                    @endphp
                    <button
                        wire:click="selectProvider('{{ $providerKey }}')"
                        class="gh-nav-item {{ $isSelected ? 'is-active' : '' }}"
                        style="width:100%; justify-content:space-between;"
                    >
                        <span>{{ $provider['label'] }}</span>
                        @if ($providerIntegration)
                            <span class="gh-badge {{ $providerIntegration->is_active ? 'gh-badge--success' : '' }}">
                                {{ $providerIntegration->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        @else
                            <span class="gh-badge">Not Configured</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title">{{ $providers[$selectedProvider]['label'] ?? ucfirst($selectedProvider) }} Configuration</div>
            <p class="gh-muted" style="font-size:12px; margin:4px 0 16px;">{{ $providers[$selectedProvider]['description'] ?? '' }}</p>

            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:16px;">
                <input type="checkbox" wire:model="isActive">
                <span style="font-weight:600; font-size:12.5px;">Set integration as active</span>
            </label>

            @if ($selectedProvider === 'whatsapp')
                @include('livewire.platform.integrations.forms.whatsapp')
            @elseif ($selectedProvider === 'email')
                @include('livewire.platform.integrations.forms.email')
            @elseif ($selectedProvider === 'flutterwave')
                @include('livewire.platform.integrations.forms.flutterwave')
            @endif

            <div style="border-top:1px solid var(--gh-hairline); margin:20px 0;"></div>

            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">Webhook URL</span>
                    <input type="url" wire:model.defer="webhookUrl" class="gh-input" style="width:100%;" placeholder="https://example.com/api/webhooks/provider">
                    @error('webhookUrl') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Webhook secret</span>
                    <input type="text" wire:model.defer="webhookSecret" class="gh-input" style="width:100%;" placeholder="Webhook signing secret">
                    @error('webhookSecret') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>

            @if ($selectedProvider === 'whatsapp' && $selectedIntegration)
                <p class="gh-eyebrow" style="margin:20px 0 10px;">Send test message</p>
                <div class="gh-grid-2">
                    <div class="gh-field">
                        <span class="gh-label">Recipient phone (E.164)</span>
                        <input type="text" wire:model.defer="testRecipientPhone" class="gh-input" style="width:100%;" placeholder="2567XXXXXXXX">
                        @error('testRecipientPhone') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Message body</span>
                        <textarea wire:model.defer="testMessageBody" rows="3" class="gh-input" style="width:100%;" placeholder="Type a short test message"></textarea>
                        @error('testMessageBody') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            @if ($selectedProvider === 'flutterwave' && $selectedIntegration)
                <p class="gh-eyebrow" style="margin:20px 0 10px;">Initiate test payment</p>
                <div class="gh-grid-2">
                    <div class="gh-field">
                        <span class="gh-label">Customer email</span>
                        <input type="email" wire:model.defer="flutterwaveTestCustomerEmail" class="gh-input" style="width:100%;" placeholder="customer@example.com">
                        @error('flutterwaveTestCustomerEmail') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Customer name</span>
                        <input type="text" wire:model.defer="flutterwaveTestCustomerName" class="gh-input" style="width:100%;" placeholder="Customer Name">
                        @error('flutterwaveTestCustomerName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Amount</span>
                        <input type="number" step="0.01" min="100" wire:model.defer="flutterwaveTestAmount" class="gh-input" style="width:100%;" placeholder="1000">
                        @error('flutterwaveTestAmount') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Currency</span>
                        <input type="text" maxlength="3" wire:model.defer="flutterwaveTestCurrency" class="gh-input" style="width:100%;" placeholder="UGX">
                        @error('flutterwaveTestCurrency') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if ($flutterwaveCheckoutLink)
                    <div class="gh-badge gh-badge--info" style="display:block; margin-top:12px; padding:10px 12px; font-size:12px; word-break:break-all;">
                        Checkout link ready:
                        <a href="{{ $flutterwaveCheckoutLink }}" target="_blank" style="color:var(--gh-primary); text-decoration:underline;">{{ $flutterwaveCheckoutLink }}</a>
                    </div>
                @endif
            @endif

            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:20px;">
                <button class="gh-btn gh-btn--primary" wire:click="save">Save integration</button>

                @if ($selectedIntegration)
                    <button class="gh-btn" wire:click="testConnection({{ $selectedIntegration->id }})">Test connection</button>
                    @if ($selectedProvider === 'whatsapp')
                        <button class="gh-btn" wire:click="sendTestMessage({{ $selectedIntegration->id }})">Send test message</button>
                    @elseif ($selectedProvider === 'flutterwave')
                        <button class="gh-btn" wire:click="initiateFlutterwaveTestPayment({{ $selectedIntegration->id }})">Initiate test payment</button>
                    @endif
                    <button class="gh-btn" wire:click="toggleStatus({{ $selectedIntegration->id }})">
                        {{ $selectedIntegration->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button class="gh-btn" style="color:var(--gh-error);" wire:click="deleteIntegration({{ $selectedIntegration->id }})" wire:confirm="Remove this integration?">
                        Remove
                    </button>
                @endif
            </div>

            @if ($selectedIntegration)
                <div class="gh-hint" style="margin-top:14px;">
                    Last tested: {{ $selectedIntegration->last_tested_at ? $selectedIntegration->last_tested_at->diffForHumans() : 'Never' }}
                </div>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Recent Logs</div>
        @if ($recentLogs->isEmpty())
            <p class="gh-muted" style="font-size:12px;">No activity logs for this provider yet.</p>
        @else
            <div class="gh-table-scroll">
                <table class="gh-table">
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
                                <td class="gh-muted">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td><span class="gh-badge">{{ $log->action }}</span></td>
                                <td>
                                    <span class="gh-badge {{ $log->status === 'success' ? 'gh-badge--success' : 'gh-badge--error' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="gh-muted">{{ $log->error_message ?: 'OK' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
