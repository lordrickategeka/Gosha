<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:20px;">
        <div>
            <div class="gh-eyebrow">Workspace configuration</div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:4px;">Settings</div>
            <p class="gh-muted" style="font-size:12.5px; max-width:560px; margin:6px 0 0;">Control business details, invoicing defaults, notification preferences, and operational taxonomies from a single workspace.</p>
        </div>
        <div class="gh-card gh-card--pad" style="width:100%; max-width:22rem;">
            <div class="gh-eyebrow">Active section</div>
            <p style="font-size:16px; font-weight:700; margin-top:6px;">
                {{ $tab === 'general' ? 'General' : '' }}
                {{ $tab === 'invoice' ? 'Invoicing' : '' }}
                {{ $tab === 'notifications' ? 'Notifications' : '' }}
                {{ $tab === 'taxonomy' ? 'Taxonomy' : '' }}
                {{ $tab === 'pricing' ? 'Pricing Plans' : '' }}
            </p>
            <p class="gh-muted" style="font-size:12px; margin-top:2px;">Changes are saved per section.</p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:240px minmax(0,1fr); gap:20px; align-items:start;">
        <aside class="gh-card gh-card--pad" style="position:sticky; top:14px;">
            <div class="gh-eyebrow" style="margin-bottom:10px;">Settings navigation</div>
            <div class="gh-stack" style="gap:6px;">
                <button wire:click="$set('tab', 'general')" class="gh-nav-item {{ $tab === 'general' ? 'is-active' : '' }}" style="text-align:left; height:auto; flex-direction:column; align-items:flex-start; gap:2px; padding:10px 12px;">
                    <span style="font-weight:700; font-size:13px;">General</span>
                    <span style="font-size:11px; opacity:.7;">Business profile and defaults</span>
                </button>
                <button wire:click="$set('tab', 'invoice')" class="gh-nav-item {{ $tab === 'invoice' ? 'is-active' : '' }}" style="text-align:left; height:auto; flex-direction:column; align-items:flex-start; gap:2px; padding:10px 12px;">
                    <span style="font-weight:700; font-size:13px;">Invoicing</span>
                    <span style="font-size:11px; opacity:.7;">Taxes, due dates, footer</span>
                </button>
                <button wire:click="$set('tab', 'notifications')" class="gh-nav-item {{ $tab === 'notifications' ? 'is-active' : '' }}" style="text-align:left; height:auto; flex-direction:column; align-items:flex-start; gap:2px; padding:10px 12px;">
                    <span style="font-weight:700; font-size:13px;">Notifications</span>
                    <span style="font-size:11px; opacity:.7;">Email and SMS channels</span>
                </button>
                <button wire:click="$set('tab', 'taxonomy')" class="gh-nav-item {{ $tab === 'taxonomy' ? 'is-active' : '' }}" style="text-align:left; height:auto; flex-direction:column; align-items:flex-start; gap:2px; padding:10px 12px;">
                    <span style="font-weight:700; font-size:13px;">Taxonomy</span>
                    <span style="font-size:11px; opacity:.7;">Categories and types</span>
                </button>
                @if(auth()->user()->isPlatformUser())
                    <button wire:click="$set('tab', 'pricing')" class="gh-nav-item {{ $tab === 'pricing' ? 'is-active' : '' }}" style="text-align:left; height:auto; flex-direction:column; align-items:flex-start; gap:2px; padding:10px 12px;">
                        <span style="font-weight:700; font-size:13px;">Pricing Plans</span>
                        <span style="font-size:11px; opacity:.7;">Platform subscription rules</span>
                    </button>
                @endif
            </div>
        </aside>

        <section class="gh-stack">
            @if($tab === 'general')
                <div class="gh-card gh-card--pad">
                    <div style="margin-bottom:20px;">
                        <div class="gh-eyebrow">Company profile</div>
                        <div class="gh-card__title" style="margin-top:4px;">General Settings</div>
                        <p class="gh-muted" style="font-size:12px; margin-top:4px;">Maintain identity, contact details, regional defaults, and business closure rules.</p>
                    </div>

                    <form wire:submit="saveGeneral" class="gh-stack">
                        <div class="gh-grid-2">
                            <div class="gh-field" style="grid-column:1/-1;">
                                <span class="gh-label">Business name *</span>
                                <input type="text" wire:model="businessName" class="gh-input" style="width:100%;">
                                @error('businessName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Phone</span>
                                <input type="text" wire:model="phone" class="gh-input" style="width:100%;">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Email</span>
                                <input type="email" wire:model="email" class="gh-input" style="width:100%;">
                            </div>
                            <div class="gh-field" style="grid-column:1/-1;">
                                <span class="gh-label">Address</span>
                                <textarea wire:model="address" rows="2" class="gh-input" style="width:100%;"></textarea>
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Currency</span>
                                <select wire:model="currency" class="gh-select" style="width:100%;">
                                    <option value="UGX">UGX - Ugandan Shilling</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="KES">KES - Kenyan Shilling</option>
                                    <option value="TZS">TZS - Tanzanian Shilling</option>
                                </select>
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Timezone</span>
                                <select wire:model="timezone" class="gh-select" style="width:100%;">
                                    <option value="Africa/Kampala">Africa/Kampala (EAT)</option>
                                    <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                                    <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                        </div>

                        <div style="border:1px solid var(--gh-base-300); background:var(--gh-base-200); border-radius:var(--gh-radius); padding:16px;">
                            <p class="gh-eyebrow" style="margin-bottom:10px;">Business closure</p>
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="checkbox" wire:model="closureEnabled">
                                <span style="font-weight:600; font-size:13px;">Enable daily order closure time</span>
                            </label>
                            <p class="gh-muted" style="font-size:11.5px; margin-top:4px;">Prevent new orders from being created after a defined cut-off time.</p>
                            <div x-data x-show="$wire.closureEnabled" style="margin-top:10px;">
                                <span class="gh-label" style="display:block; margin-bottom:4px;">Closure time (24-hour)</span>
                                <input type="time" wire:model="closureTime" class="gh-input" style="width:11rem;">
                            </div>
                        </div>

                        <div style="display:flex; justify-content:flex-end;">
                            <button type="submit" class="gh-btn gh-btn--primary">Save general settings</button>
                        </div>
                    </form>
                </div>
            @endif

            @if($tab === 'invoice')
                <div class="gh-card gh-card--pad">
                    <div style="margin-bottom:20px;">
                        <div class="gh-eyebrow">Billing defaults</div>
                        <div class="gh-card__title" style="margin-top:4px;">Invoice Settings</div>
                        <p class="gh-muted" style="font-size:12px; margin-top:4px;">Set numbering patterns, due-date behavior, taxes, and document footer notes.</p>
                    </div>

                    <form wire:submit="saveInvoice" class="gh-stack">
                        <div class="gh-grid-2">
                            <div class="gh-field">
                                <span class="gh-label">Invoice number prefix</span>
                                <input type="text" wire:model="invoicePrefix" class="gh-input" style="width:100%;" placeholder="INV">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Default due days</span>
                                <input type="number" wire:model="defaultDueDays" class="gh-input" style="width:100%;" min="0">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Default tax rate (%)</span>
                                <input type="number" wire:model="taxRate" class="gh-input" style="width:100%;" min="0" max="100">
                            </div>
                            <div class="gh-field" style="grid-column:1/-1;">
                                <span class="gh-label">Invoice footer text</span>
                                <textarea wire:model="invoiceFooter" rows="3" class="gh-input" style="width:100%;" placeholder="Thank you for your business!"></textarea>
                            </div>
                        </div>
                        <div style="display:flex; justify-content:flex-end;">
                            <button type="submit" class="gh-btn gh-btn--primary">Save invoice settings</button>
                        </div>
                    </form>
                </div>
            @endif

            @if($tab === 'pricing' && auth()->user()->isPlatformUser())
                <div class="gh-card gh-card--pad">
                    <div style="margin-bottom:20px;">
                        <div class="gh-eyebrow">Platform billing</div>
                        <div class="gh-card__title" style="margin-top:4px;">Pricing Plans</div>
                        <p class="gh-muted" style="font-size:12px; margin-top:4px;">Manage platform subscriptions, usage pricing, and customer plan structures.</p>
                    </div>
                    <a href="{{ route('platform.pricing') }}" class="gh-btn gh-btn--primary">Manage pricing plans</a>
                </div>
            @endif

            @if($tab === 'notifications')
                <div class="gh-card gh-card--pad">
                    <div style="margin-bottom:20px;">
                        <div class="gh-eyebrow">Communication channels</div>
                        <div class="gh-card__title" style="margin-top:4px;">Notification Settings</div>
                        <p class="gh-muted" style="font-size:12px; margin-top:4px;">Choose how customers and staff receive updates for billing and appointments.</p>
                    </div>

                    <form wire:submit="saveNotifications" class="gh-stack">
                        <div style="border:1px solid var(--gh-base-300); border-radius:var(--gh-radius); padding:14px;">
                            <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                                <input type="checkbox" wire:model="emailNotifications">
                                <span>
                                    <span style="font-weight:600; font-size:13px;">Email notifications</span>
                                    <p class="gh-muted" style="font-size:11.5px; margin-top:2px;">Send invoice and appointment notifications by email.</p>
                                </span>
                            </label>
                        </div>
                        <div style="border:1px solid var(--gh-base-300); border-radius:var(--gh-radius); padding:14px;">
                            <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                                <input type="checkbox" wire:model="smsNotifications">
                                <span>
                                    <span style="font-weight:600; font-size:13px;">SMS notifications</span>
                                    <p class="gh-muted" style="font-size:11.5px; margin-top:2px;">Send appointment reminders and short urgent alerts by SMS.</p>
                                </span>
                            </label>
                        </div>
                        <div style="display:flex; justify-content:flex-end;">
                            <button type="submit" class="gh-btn gh-btn--primary">Save notification settings</button>
                        </div>
                    </form>
                </div>
            @endif

            @if($tab === 'taxonomy')
                <livewire:settings.taxonomy-component />
            @endif
        </section>
    </div>
</div>
