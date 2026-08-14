<div class="gh-page">
    @php
        $wizardSteps = [
            1 => ['title' => 'Business', 'note' => 'Company details'],
            2 => ['title' => 'Branch', 'note' => 'Primary location'],
            3 => ['title' => 'Owner', 'note' => 'Account login'],
            4 => ['title' => 'Billing', 'note' => 'Pricing model'],
            5 => ['title' => 'Review', 'note' => 'Confirm and create'],
        ];
        $progress = (int) round(($currentStep / $totalSteps) * 100);
    @endphp

    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('platform.vendors.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Create New Vendor</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Set up a new vendor on the platform</p>
        </div>
    </div>

    <div class="gh-meter"><div class="gh-meter__fill" style="width: {{ $progress }}%;"></div></div>

    <div class="gh-steps" style="flex-wrap:wrap;">
        @foreach($wizardSteps as $stepNumber => $meta)
            <button
                type="button"
                wire:click="goToStep({{ $stepNumber }})"
                class="gh-steps__item {{ $currentStep === $stepNumber ? 'is-active' : '' }} {{ $currentStep > $stepNumber ? 'is-done' : '' }}"
                style="border:0; background:none; cursor:pointer; padding:0;"
                @if($stepNumber > $currentStep) disabled @endif
            >
                <span class="gh-steps__num">{{ $stepNumber }}</span>
                <span style="text-align:left;">
                    <span style="display:block; font-size:12.5px; font-weight:700;">{{ $meta['title'] }}</span>
                    <span class="gh-muted" style="font-size:11px;">{{ $meta['note'] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <div class="gh-card gh-card--pad">

        {{-- Step 1: Business Details --}}
        @if($currentStep === 1)
            <div class="gh-card__title" style="margin-bottom:14px;">Business Details</div>
            <div class="gh-grid-2">
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Business name *</span>
                    <input type="text" wire:model="vendor_name" class="gh-input" style="width:100%;" placeholder="e.g. AutoCare Garage">
                    @error('vendor_name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Email *</span>
                    <input type="email" wire:model="vendor_email" class="gh-input" style="width:100%;" placeholder="info@business.com">
                    @error('vendor_email') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Phone</span>
                    <input type="text" wire:model="vendor_phone" class="gh-input" style="width:100%;" placeholder="+256 700 000000">
                    @error('vendor_phone') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Address</span>
                    <textarea wire:model="vendor_address" rows="2" class="gh-input" style="width:100%;" placeholder="Physical address"></textarea>
                    @error('vendor_address') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        {{-- Step 2: Main Branch --}}
        @if($currentStep === 2)
            <div style="margin-bottom:14px;">
                <div class="gh-card__title">Main Branch</div>
                <p class="gh-muted" style="font-size:12px; margin-top:4px;">Set up the vendor's primary branch location.</p>
            </div>
            <div class="gh-grid-2">
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Branch name *</span>
                    <input type="text" wire:model="branch_name" class="gh-input" style="width:100%;" placeholder="e.g. Main Branch - Kampala">
                    @error('branch_name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Branch email</span>
                    <input type="email" wire:model="branch_email" class="gh-input" style="width:100%;" placeholder="branch@business.com">
                    @error('branch_email') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Branch phone</span>
                    <input type="text" wire:model="branch_phone" class="gh-input" style="width:100%;" placeholder="+256 700 000000">
                    @error('branch_phone') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Branch address</span>
                    <textarea wire:model="branch_address" rows="2" class="gh-input" style="width:100%;" placeholder="Branch physical address"></textarea>
                    @error('branch_address') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        {{-- Step 3: Owner Account --}}
        @if($currentStep === 3)
            <div style="margin-bottom:14px;">
                <div class="gh-card__title">Owner Account</div>
                <p class="gh-muted" style="font-size:12px; margin-top:4px;">Create the vendor owner's login. A temporary password will be auto-generated.</p>
            </div>
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">Owner name *</span>
                    <input type="text" wire:model="owner_name" class="gh-input" style="width:100%;" placeholder="Full name">
                    @error('owner_name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Owner email *</span>
                    <input type="email" wire:model="owner_email" class="gh-input" style="width:100%;" placeholder="owner@business.com">
                    @error('owner_email') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="gh-badge gh-badge--info" style="margin-top:14px; padding:10px 12px; display:block; font-size:12px;">
                A temporary password will be generated and shown after creation. Share it securely with the vendor owner.
            </div>
        @endif

        {{-- Step 4: Billing Configuration --}}
        @if($currentStep === 4)
            <div class="gh-card__title" style="margin-bottom:14px;">Billing Configuration</div>
            <div class="gh-grid-2">
                <div class="gh-field">
                    <span class="gh-label">Billing model *</span>
                    <select wire:model.live="billing_model" class="gh-select" style="width:100%;">
                        <option value="none">None (Configure Later)</option>
                        <option value="subscription">Subscription</option>
                        <option value="transaction_fee">Transaction Fee</option>
                        <option value="commission_cut">Commission Cut</option>
                        <option value="hybrid">Hybrid</option>
                    </select>
                    @error('billing_model') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Trial period (days) *</span>
                    <input type="number" wire:model="trial_days" class="gh-input" style="width:100%;" min="0" max="365">
                    @error('trial_days') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>

                @if(in_array($billing_model, ['subscription', 'hybrid']))
                    <div class="gh-field">
                        <span class="gh-label">Subscription amount</span>
                        <input type="number" wire:model="subscription_amount" class="gh-input" style="width:100%;" step="0.01" placeholder="e.g. 150000">
                        @error('subscription_amount') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Billing interval</span>
                        <select wire:model="subscription_interval" class="gh-select" style="width:100%;">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                @endif

                @if(in_array($billing_model, ['transaction_fee', 'hybrid']))
                    <div class="gh-field">
                        <span class="gh-label">Transaction fee (%)</span>
                        <input type="number" wire:model="transaction_fee_percent" class="gh-input" style="width:100%;" step="0.01" placeholder="e.g. 2.5">
                        @error('transaction_fee_percent') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Transaction fee (flat)</span>
                        <input type="number" wire:model="transaction_fee_flat" class="gh-input" style="width:100%;" step="0.01" placeholder="e.g. 500">
                        @error('transaction_fee_flat') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if(in_array($billing_model, ['commission_cut', 'hybrid']))
                    <div class="gh-field">
                        <span class="gh-label">Commission (%)</span>
                        <input type="number" wire:model="commission_percent" class="gh-input" style="width:100%;" step="0.01" placeholder="e.g. 5">
                        @error('commission_percent') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        @endif

        {{-- Step 5: Review & Confirm --}}
        @if($currentStep === 5)
            <div style="margin-bottom:16px;">
                <div class="gh-card__title">Review &amp; Confirm</div>
                <p class="gh-muted" style="font-size:12px; margin-top:4px;">Please review all details before creating the vendor.</p>
            </div>

            <div class="gh-grid-2">
                <div style="border:1px solid var(--gh-hairline); border-radius:var(--gh-radius); padding:14px;">
                    <p style="font-weight:700; font-size:13px; margin-bottom:8px;">Business Details</p>
                    <dl class="gh-stack" style="gap:5px; font-size:12.5px;">
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Name</dt><dd style="font-weight:600;">{{ $vendor_name }}</dd></div>
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Email</dt><dd>{{ $vendor_email }}</dd></div>
                        @if($vendor_phone)<div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Phone</dt><dd>{{ $vendor_phone }}</dd></div>@endif
                        @if($vendor_address)<div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Address</dt><dd>{{ $vendor_address }}</dd></div>@endif
                    </dl>
                </div>

                <div style="border:1px solid var(--gh-hairline); border-radius:var(--gh-radius); padding:14px;">
                    <p style="font-weight:700; font-size:13px; margin-bottom:8px;">Main Branch</p>
                    <dl class="gh-stack" style="gap:5px; font-size:12.5px;">
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Name</dt><dd style="font-weight:600;">{{ $branch_name }}</dd></div>
                        @if($branch_email)<div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Email</dt><dd>{{ $branch_email }}</dd></div>@endif
                        @if($branch_phone)<div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Phone</dt><dd>{{ $branch_phone }}</dd></div>@endif
                        @if($branch_address)<div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Address</dt><dd>{{ $branch_address }}</dd></div>@endif
                    </dl>
                </div>

                <div style="border:1px solid var(--gh-hairline); border-radius:var(--gh-radius); padding:14px;">
                    <p style="font-weight:700; font-size:13px; margin-bottom:8px;">Owner Account</p>
                    <dl class="gh-stack" style="gap:5px; font-size:12.5px;">
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Name</dt><dd style="font-weight:600;">{{ $owner_name }}</dd></div>
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Email</dt><dd>{{ $owner_email }}</dd></div>
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Password</dt><dd class="gh-hint">Auto-generated</dd></div>
                    </dl>
                </div>

                <div style="border:1px solid var(--gh-hairline); border-radius:var(--gh-radius); padding:14px;">
                    <p style="font-weight:700; font-size:13px; margin-bottom:8px;">Billing</p>
                    <dl class="gh-stack" style="gap:5px; font-size:12.5px;">
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Model</dt><dd style="font-weight:600;">{{ ucfirst(str_replace('_', ' ', $billing_model)) }}</dd></div>
                        <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Trial period</dt><dd>{{ $trial_days }} days</dd></div>
                        @if(in_array($billing_model, ['subscription', 'hybrid']) && $subscription_amount)
                            <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Subscription</dt><dd>UGX {{ number_format($subscription_amount) }} / {{ $subscription_interval }}</dd></div>
                        @endif
                        @if(in_array($billing_model, ['transaction_fee', 'hybrid']))
                            @if($transaction_fee_percent)<div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Txn fee</dt><dd>{{ $transaction_fee_percent }}%{{ $transaction_fee_flat ? ' + UGX ' . number_format($transaction_fee_flat) : '' }}</dd></div>@endif
                        @endif
                        @if(in_array($billing_model, ['commission_cut', 'hybrid']) && $commission_percent)
                            <div style="display:flex; justify-content:space-between;"><dt class="gh-muted">Commission</dt><dd>{{ $commission_percent }}%</dd></div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        <div style="display:flex; justify-content:space-between; border-top:1px solid var(--gh-hairline); padding-top:16px; margin-top:20px;">
            @if($currentStep > 1)
                <button wire:click="previousStep" class="gh-btn">← Previous</button>
            @else
                <div></div>
            @endif

            @if($currentStep < $totalSteps)
                <button wire:click="nextStep" class="gh-btn gh-btn--primary">Next →</button>
            @else
                <button wire:click="save" class="gh-btn gh-btn--primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Create vendor</span>
                    <span wire:loading wire:target="save">Creating…</span>
                </button>
            @endif
        </div>
    </div>
</div>
