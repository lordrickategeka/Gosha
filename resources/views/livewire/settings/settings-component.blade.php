<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Settings</h1>
        <p class="text-base-content/60">Configure your garage system</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar -->
        <div class="lg:w-64">
            <ul class="menu bg-base-100 rounded-lg shadow-sm">
                <li><button wire:click="$set('tab', 'general')" class="{{ $tab === 'general' ? 'active' : '' }}">General</button></li>
                <li><button wire:click="$set('tab', 'invoice')" class="{{ $tab === 'invoice' ? 'active' : '' }}">Invoicing</button></li>
                <li><button wire:click="$set('tab', 'notifications')" class="{{ $tab === 'notifications' ? 'active' : '' }}">Notifications</button></li>
            </ul>
        </div>

        <!-- Content -->
        <div class="flex-1">
            @if($tab === 'general')
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">General Settings</h2>
                        <form wire:submit="saveGeneral">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="form-control sm:col-span-2">
                                    <label class="label"><span class="label-text font-medium">Business Name *</span></label>
                                    <input type="text" wire:model="businessName" class="input input-bordered" />
                                    @error('businessName') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Phone</span></label>
                                    <input type="text" wire:model="phone" class="input input-bordered" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Email</span></label>
                                    <input type="email" wire:model="email" class="input input-bordered" />
                                </div>
                                <div class="form-control sm:col-span-2">
                                    <label class="label"><span class="label-text font-medium">Address</span></label>
                                    <textarea wire:model="address" rows="2" class="textarea textarea-bordered"></textarea>
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Currency</span></label>
                                    <select wire:model="currency" class="select select-bordered">
                                        <option value="UGX">UGX - Ugandan Shilling</option>
                                        <option value="USD">USD - US Dollar</option>
                                        <option value="KES">KES - Kenyan Shilling</option>
                                        <option value="TZS">TZS - Tanzanian Shilling</option>
                                    </select>
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Timezone</span></label>
                                    <select wire:model="timezone" class="select select-bordered">
                                        <option value="Africa/Kampala">Africa/Kampala (EAT)</option>
                                        <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                                        <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
                                        <option value="UTC">UTC</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-6">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($tab === 'invoice')
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Invoice Settings</h2>
                        <form wire:submit="saveInvoice">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Invoice Number Prefix</span></label>
                                    <input type="text" wire:model="invoicePrefix" class="input input-bordered" placeholder="INV" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Default Due Days</span></label>
                                    <input type="number" wire:model="defaultDueDays" class="input input-bordered" min="0" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Default Tax Rate (%)</span></label>
                                    <input type="number" wire:model="taxRate" class="input input-bordered" min="0" max="100" />
                                </div>
                                <div class="form-control sm:col-span-2">
                                    <label class="label"><span class="label-text font-medium">Invoice Footer Text</span></label>
                                    <textarea wire:model="invoiceFooter" rows="2" class="textarea textarea-bordered" placeholder="Thank you for your business!"></textarea>
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-6">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($tab === 'notifications')
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Notification Settings</h2>
                        <form wire:submit="saveNotifications">
                            <div class="space-y-4">
                                <div class="form-control">
                                    <label class="label cursor-pointer justify-start gap-4">
                                        <input type="checkbox" wire:model="emailNotifications" class="toggle toggle-primary" />
                                        <div>
                                            <span class="label-text font-medium">Email Notifications</span>
                                            <p class="text-xs text-base-content/60">Send invoice and appointment notifications via email</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-control">
                                    <label class="label cursor-pointer justify-start gap-4">
                                        <input type="checkbox" wire:model="smsNotifications" class="toggle toggle-primary" />
                                        <div>
                                            <span class="label-text font-medium">SMS Notifications</span>
                                            <p class="text-xs text-base-content/60">Send appointment reminders via SMS</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-6">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
