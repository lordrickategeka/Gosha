<div class="space-y-6">
    <div class="app-page-header">
        <div>
            <p class="app-kicker">Workspace configuration</p>
            <h1 class="app-title">Settings</h1>
            <p class="app-subtitle">Control business details, invoicing defaults, notification preferences, and operational taxonomies from a single workspace.</p>
        </div>
        <div class="app-mini-card w-full max-w-sm">
            <p class="app-eyebrow">Active section</p>
            <p class="mt-2 text-lg font-semibold">
                {{ $tab === 'general' ? 'General' : '' }}
                {{ $tab === 'invoice' ? 'Invoicing' : '' }}
                {{ $tab === 'notifications' ? 'Notifications' : '' }}
                {{ $tab === 'taxonomy' ? 'Taxonomy' : '' }}
                {{ $tab === 'pricing' ? 'Pricing Plans' : '' }}
            </p>
            <p class="mt-1 text-sm text-base-content/60">Changes are saved per section.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
        <aside class="app-panel-subtle p-4 xl:sticky xl:top-24 xl:self-start">
            <p class="app-eyebrow mb-3">Settings navigation</p>
            <div class="space-y-2">
                <button wire:click="$set('tab', 'general')" class="w-full rounded-xl border px-4 py-3 text-left transition {{ $tab === 'general' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-transparent bg-base-100 text-base-content hover:border-base-300 hover:bg-base-200/70' }}">
                    <span class="block text-sm font-semibold">General</span>
                    <span class="mt-1 block text-xs opacity-70">Business profile and defaults</span>
                </button>
                <button wire:click="$set('tab', 'invoice')" class="w-full rounded-xl border px-4 py-3 text-left transition {{ $tab === 'invoice' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-transparent bg-base-100 text-base-content hover:border-base-300 hover:bg-base-200/70' }}">
                    <span class="block text-sm font-semibold">Invoicing</span>
                    <span class="mt-1 block text-xs opacity-70">Taxes, due dates, footer</span>
                </button>
                <button wire:click="$set('tab', 'notifications')" class="w-full rounded-xl border px-4 py-3 text-left transition {{ $tab === 'notifications' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-transparent bg-base-100 text-base-content hover:border-base-300 hover:bg-base-200/70' }}">
                    <span class="block text-sm font-semibold">Notifications</span>
                    <span class="mt-1 block text-xs opacity-70">Email and SMS channels</span>
                </button>
                <button wire:click="$set('tab', 'taxonomy')" class="w-full rounded-xl border px-4 py-3 text-left transition {{ $tab === 'taxonomy' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-transparent bg-base-100 text-base-content hover:border-base-300 hover:bg-base-200/70' }}">
                    <span class="block text-sm font-semibold">Taxonomy</span>
                    <span class="mt-1 block text-xs opacity-70">Categories and types</span>
                </button>
                @if(auth()->user()->isPlatformUser())
                    <button wire:click="$set('tab', 'pricing')" class="w-full rounded-xl border px-4 py-3 text-left transition {{ $tab === 'pricing' ? 'border-primary/40 bg-primary/10 text-primary shadow-sm' : 'border-transparent bg-base-100 text-base-content hover:border-base-300 hover:bg-base-200/70' }}">
                        <span class="block text-sm font-semibold">Pricing Plans</span>
                        <span class="mt-1 block text-xs opacity-70">Platform subscription rules</span>
                    </button>
                @endif
            </div>
        </aside>

        <section class="space-y-6">
            @if($tab === 'general')
                <div class="app-panel p-6 lg:p-7">
                    <div class="mb-6">
                        <p class="app-eyebrow">Company profile</p>
                        <h2 class="text-xl font-semibold tracking-[-0.02em]">General Settings</h2>
                        <p class="mt-1 text-sm text-base-content/60">Maintain identity, contact details, regional defaults, and business closure rules.</p>
                    </div>

                    <form wire:submit="saveGeneral" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control sm:col-span-2">
                                <label class="block text-sm font-medium mb-2">Business Name *</label>
                                <input type="text" wire:model="businessName" class="input input-bordered" />
                                @error('businessName') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Phone</label>
                                <input type="text" wire:model="phone" class="input input-bordered" />
                            </div>
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Email</label>
                                <input type="email" wire:model="email" class="input input-bordered" />
                            </div>
                            <div class="form-control sm:col-span-2">
                                <label class="block text-sm font-medium mb-2">Address</label>
                                <textarea wire:model="address" rows="2" class="textarea textarea-bordered"></textarea>
                            </div>
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Currency</label>
                                <select wire:model="currency" class="select select-bordered">
                                    <option value="UGX">UGX - Ugandan Shilling</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="KES">KES - Kenyan Shilling</option>
                                    <option value="TZS">TZS - Tanzanian Shilling</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Timezone</label>
                                <select wire:model="timezone" class="select select-bordered">
                                    <option value="Africa/Kampala">Africa/Kampala (EAT)</option>
                                    <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                                    <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                        </div>

                        <div class="rounded-lg border border-base-300 bg-base-200/40 p-4 sm:p-5 space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-base-content/50">Business closure</p>
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3 px-0">
                                    <input type="checkbox" wire:model="closureEnabled" class="toggle toggle-primary" />
                                    <span class="label-text font-medium">Enable daily order closure time</span>
                                </label>
                                <p class="text-xs text-base-content/60">Prevent new orders from being created after a defined cut-off time.</p>
                            </div>
                            <div class="form-control" x-data x-show="$wire.closureEnabled">
                                <label class="block text-sm font-medium mb-2">Closure Time (24-hour)</label>
                                <input type="time" wire:model="closureTime" class="input input-bordered w-44" />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary rounded-lg px-5">Save General Settings</button>
                        </div>
                    </form>
                </div>
            @endif

            @if($tab === 'invoice')
                <div class="app-panel p-6 lg:p-7">
                    <div class="mb-6">
                        <p class="app-eyebrow">Billing defaults</p>
                        <h2 class="text-xl font-semibold tracking-[-0.02em]">Invoice Settings</h2>
                        <p class="mt-1 text-sm text-base-content/60">Set numbering patterns, due-date behavior, taxes, and document footer notes.</p>
                    </div>

                    <form wire:submit="saveInvoice" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Invoice Number Prefix</label>
                                <input type="text" wire:model="invoicePrefix" class="input input-bordered" placeholder="INV" />
                            </div>
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Default Due Days</label>
                                <input type="number" wire:model="defaultDueDays" class="input input-bordered" min="0" />
                            </div>
                            <div class="form-control">
                                <label class="block text-sm font-medium mb-2">Default Tax Rate (%)</label>
                                <input type="number" wire:model="taxRate" class="input input-bordered" min="0" max="100" />
                            </div>
                            <div class="form-control sm:col-span-2">
                                <label class="block text-sm font-medium mb-2">Invoice Footer Text</label>
                                <textarea wire:model="invoiceFooter" rows="3" class="textarea textarea-bordered" placeholder="Thank you for your business!"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary rounded-lg px-5">Save Invoice Settings</button>
                        </div>
                    </form>
                </div>
            @endif

            @if($tab === 'pricing' && auth()->user()->isPlatformUser())
                <div class="app-panel p-6 lg:p-7">
                    <div class="mb-6">
                        <p class="app-eyebrow">Platform billing</p>
                        <h2 class="text-xl font-semibold tracking-[-0.02em]">Pricing Plans</h2>
                        <p class="mt-1 text-sm text-base-content/60">Manage platform subscriptions, usage pricing, and customer plan structures.</p>
                    </div>

                    <a href="{{ route('platform.pricing') }}" class="btn btn-primary rounded-lg px-5">
                        Manage Pricing Plans
                    </a>
                </div>
            @endif

            @if($tab === 'notifications')
                <div class="app-panel p-6 lg:p-7">
                    <div class="mb-6">
                        <p class="app-eyebrow">Communication channels</p>
                        <h2 class="text-xl font-semibold tracking-[-0.02em]">Notification Settings</h2>
                        <p class="mt-1 text-sm text-base-content/60">Choose how customers and staff receive updates for billing and appointments.</p>
                    </div>

                    <form wire:submit="saveNotifications" class="space-y-4">
                        <div class="rounded-lg border border-base-300 bg-base-100 p-4">
                            <label class="label cursor-pointer justify-start gap-4 px-0">
                                <input type="checkbox" wire:model="emailNotifications" class="toggle toggle-primary" />
                                <div>
                                    <span class="font-medium">Email Notifications</span>
                                    <p class="text-xs text-base-content/60">Send invoice and appointment notifications by email.</p>
                                </div>
                            </label>
                        </div>

                        <div class="rounded-lg border border-base-300 bg-base-100 p-4">
                            <label class="label cursor-pointer justify-start gap-4 px-0">
                                <input type="checkbox" wire:model="smsNotifications" class="toggle toggle-primary" />
                                <div>
                                    <span class="font-medium">SMS Notifications</span>
                                    <p class="text-xs text-base-content/60">Send appointment reminders and short urgent alerts by SMS.</p>
                                </div>
                            </label>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn btn-primary rounded-lg px-5">Save Notification Settings</button>
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
