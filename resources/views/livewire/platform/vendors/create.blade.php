<div>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('platform.vendors.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Create New Vendor</h1>
            <p class="text-base-content/60">Set up a new vendor on the platform</p>
        </div>
    </div>

    <!-- Steps Indicator -->
    <ul class="steps steps-horizontal w-full mb-8">
        <li class="step {{ $currentStep >= 1 ? 'step-primary' : '' }}" wire:click="goToStep(1)">
            <span class="hidden sm:inline">Business</span>
        </li>
        <li class="step {{ $currentStep >= 2 ? 'step-primary' : '' }}" wire:click="goToStep(2)">
            <span class="hidden sm:inline">Branch</span>
        </li>
        <li class="step {{ $currentStep >= 3 ? 'step-primary' : '' }}" wire:click="goToStep(3)">
            <span class="hidden sm:inline">Owner</span>
        </li>
        <li class="step {{ $currentStep >= 4 ? 'step-primary' : '' }}" wire:click="goToStep(4)">
            <span class="hidden sm:inline">Billing</span>
        </li>
        <li class="step {{ $currentStep >= 5 ? 'step-primary' : '' }}">
            <span class="hidden sm:inline">Review</span>
        </li>
    </ul>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">

            {{-- Step 1: Business Details --}}
            @if($currentStep === 1)
                <h2 class="card-title mb-4">Business Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text">Business Name <span class="text-error">*</span></span></label>
                        <input type="text" wire:model="vendor_name" class="input input-bordered" placeholder="e.g. AutoCare Garage" />
                        @error('vendor_name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Email <span class="text-error">*</span></span></label>
                        <input type="email" wire:model="vendor_email" class="input input-bordered" placeholder="info@business.com" />
                        @error('vendor_email') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Phone</span></label>
                        <input type="text" wire:model="vendor_phone" class="input input-bordered" placeholder="+256 700 000000" />
                        @error('vendor_phone') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text">Address</span></label>
                        <textarea wire:model="vendor_address" class="textarea textarea-bordered" rows="2" placeholder="Physical address"></textarea>
                        @error('vendor_address') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            {{-- Step 2: Main Branch --}}
            @if($currentStep === 2)
                <h2 class="card-title mb-4">Main Branch</h2>
                <p class="text-base-content/60 text-sm mb-4">Set up the vendor's primary branch location.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text">Branch Name <span class="text-error">*</span></span></label>
                        <input type="text" wire:model="branch_name" class="input input-bordered" placeholder="e.g. Main Branch - Kampala" />
                        @error('branch_name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Branch Email</span></label>
                        <input type="email" wire:model="branch_email" class="input input-bordered" placeholder="branch@business.com" />
                        @error('branch_email') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Branch Phone</span></label>
                        <input type="text" wire:model="branch_phone" class="input input-bordered" placeholder="+256 700 000000" />
                        @error('branch_phone') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text">Branch Address</span></label>
                        <textarea wire:model="branch_address" class="textarea textarea-bordered" rows="2" placeholder="Branch physical address"></textarea>
                        @error('branch_address') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            {{-- Step 3: Owner Account --}}
            @if($currentStep === 3)
                <h2 class="card-title mb-4">Owner Account</h2>
                <p class="text-base-content/60 text-sm mb-4">Create the vendor owner's login. A temporary password will be auto-generated.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Owner Name <span class="text-error">*</span></span></label>
                        <input type="text" wire:model="owner_name" class="input input-bordered" placeholder="Full name" />
                        @error('owner_name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Owner Email <span class="text-error">*</span></span></label>
                        <input type="email" wire:model="owner_email" class="input input-bordered" placeholder="owner@business.com" />
                        @error('owner_email') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="alert alert-info mt-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>A temporary password will be generated and shown after creation. Share it securely with the vendor owner.</span>
                </div>
            @endif

            {{-- Step 4: Billing Configuration --}}
            @if($currentStep === 4)
                <h2 class="card-title mb-4">Billing Configuration</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Billing Model <span class="text-error">*</span></span></label>
                        <select wire:model.live="billing_model" class="select select-bordered">
                            <option value="none">None (Configure Later)</option>
                            <option value="subscription">Subscription</option>
                            <option value="transaction_fee">Transaction Fee</option>
                            <option value="commission_cut">Commission Cut</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                        @error('billing_model') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Trial Period (days) <span class="text-error">*</span></span></label>
                        <input type="number" wire:model="trial_days" class="input input-bordered" min="0" max="365" />
                        @error('trial_days') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if(in_array($billing_model, ['subscription', 'hybrid']))
                        <div class="form-control">
                            <label class="label"><span class="label-text">Subscription Amount</span></label>
                            <input type="number" wire:model="subscription_amount" class="input input-bordered" step="0.01" placeholder="e.g. 150000" />
                            @error('subscription_amount') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Billing Interval</span></label>
                            <select wire:model="subscription_interval" class="select select-bordered">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    @endif

                    @if(in_array($billing_model, ['transaction_fee', 'hybrid']))
                        <div class="form-control">
                            <label class="label"><span class="label-text">Transaction Fee (%)</span></label>
                            <input type="number" wire:model="transaction_fee_percent" class="input input-bordered" step="0.01" placeholder="e.g. 2.5" />
                            @error('transaction_fee_percent') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Transaction Fee (flat)</span></label>
                            <input type="number" wire:model="transaction_fee_flat" class="input input-bordered" step="0.01" placeholder="e.g. 500" />
                            @error('transaction_fee_flat') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    @if(in_array($billing_model, ['commission_cut', 'hybrid']))
                        <div class="form-control">
                            <label class="label"><span class="label-text">Commission (%)</span></label>
                            <input type="number" wire:model="commission_percent" class="input input-bordered" step="0.01" placeholder="e.g. 5" />
                            @error('commission_percent') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            @endif

            {{-- Step 5: Review & Confirm --}}
            @if($currentStep === 5)
                <h2 class="card-title mb-4">Review & Confirm</h2>
                <p class="text-base-content/60 text-sm mb-6">Please review all details before creating the vendor.</p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Business Details -->
                    <div class="border border-base-300 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                            Business Details
                        </h3>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between"><dt class="text-base-content/60">Name</dt><dd class="font-medium">{{ $vendor_name }}</dd></div>
                            <div class="flex justify-between"><dt class="text-base-content/60">Email</dt><dd>{{ $vendor_email }}</dd></div>
                            @if($vendor_phone)<div class="flex justify-between"><dt class="text-base-content/60">Phone</dt><dd>{{ $vendor_phone }}</dd></div>@endif
                            @if($vendor_address)<div class="flex justify-between"><dt class="text-base-content/60">Address</dt><dd>{{ $vendor_address }}</dd></div>@endif
                        </dl>
                    </div>

                    <!-- Main Branch -->
                    <div class="border border-base-300 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Main Branch
                        </h3>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between"><dt class="text-base-content/60">Name</dt><dd class="font-medium">{{ $branch_name }}</dd></div>
                            @if($branch_email)<div class="flex justify-between"><dt class="text-base-content/60">Email</dt><dd>{{ $branch_email }}</dd></div>@endif
                            @if($branch_phone)<div class="flex justify-between"><dt class="text-base-content/60">Phone</dt><dd>{{ $branch_phone }}</dd></div>@endif
                            @if($branch_address)<div class="flex justify-between"><dt class="text-base-content/60">Address</dt><dd>{{ $branch_address }}</dd></div>@endif
                        </dl>
                    </div>

                    <!-- Owner Account -->
                    <div class="border border-base-300 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Owner Account
                        </h3>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between"><dt class="text-base-content/60">Name</dt><dd class="font-medium">{{ $owner_name }}</dd></div>
                            <div class="flex justify-between"><dt class="text-base-content/60">Email</dt><dd>{{ $owner_email }}</dd></div>
                            <div class="flex justify-between"><dt class="text-base-content/60">Password</dt><dd class="italic text-base-content/50">Auto-generated</dd></div>
                        </dl>
                    </div>

                    <!-- Billing -->
                    <div class="border border-base-300 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            Billing
                        </h3>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between"><dt class="text-base-content/60">Model</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $billing_model)) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-base-content/60">Trial Period</dt><dd>{{ $trial_days }} days</dd></div>
                            @if(in_array($billing_model, ['subscription', 'hybrid']) && $subscription_amount)
                                <div class="flex justify-between"><dt class="text-base-content/60">Subscription</dt><dd>UGX {{ number_format($subscription_amount) }} / {{ $subscription_interval }}</dd></div>
                            @endif
                            @if(in_array($billing_model, ['transaction_fee', 'hybrid']))
                                @if($transaction_fee_percent)<div class="flex justify-between"><dt class="text-base-content/60">Txn Fee</dt><dd>{{ $transaction_fee_percent }}%{{ $transaction_fee_flat ? ' + UGX ' . number_format($transaction_fee_flat) : '' }}</dd></div>@endif
                            @endif
                            @if(in_array($billing_model, ['commission_cut', 'hybrid']) && $commission_percent)
                                <div class="flex justify-between"><dt class="text-base-content/60">Commission</dt><dd>{{ $commission_percent }}%</dd></div>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="divider"></div>
            <div class="flex justify-between">
                @if($currentStep > 1)
                    <button wire:click="previousStep" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Previous
                    </button>
                @else
                    <div></div>
                @endif

                @if($currentStep < $totalSteps)
                    <button wire:click="nextStep" class="btn btn-primary">
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                @else
                    <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Create Vendor</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    </button>
                @endif
            </div>

        </div>
    </div>
</div>
