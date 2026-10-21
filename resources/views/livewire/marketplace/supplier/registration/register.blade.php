<div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-1">Become a Supplier</h2>
            <p class="text-base-content/60 mb-6">List your parts and stock on the GarageHQ marketplace.</p>

            <!-- Steps -->
            <ul class="steps steps-horizontal w-full mb-6 text-xs">
                <li class="step {{ $currentStep >= 1 ? 'step-primary' : '' }}" wire:click="goToStep(1)">Business</li>
                <li class="step {{ $currentStep >= 2 ? 'step-primary' : '' }}" wire:click="goToStep(2)">Your login</li>
                <li class="step {{ $currentStep >= 3 ? 'step-primary' : '' }}">Review</li>
            </ul>

            {{-- Step 1: Business details --}}
            @if($currentStep === 1)
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Business name <span class="text-error">*</span></span></label>
                        <input type="text" wire:model="business_name" class="input input-bordered w-full @error('business_name') input-error @enderror" placeholder="e.g. Kampala Auto Parts Ltd" />
                        @error('business_name') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Business email <span class="text-error">*</span></span></label>
                        <input type="email" wire:model="business_email" class="input input-bordered w-full @error('business_email') input-error @enderror" placeholder="sales@yourbusiness.com" />
                        @error('business_email') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Phone</span></label>
                        <input type="text" wire:model="business_phone" class="input input-bordered w-full" placeholder="+256 700 000000" />
                        @error('business_phone') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Address</span></label>
                        <textarea wire:model="business_address" class="textarea textarea-bordered w-full" rows="2" placeholder="Physical / warehouse address"></textarea>
                        @error('business_address') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                </div>
            @endif

            {{-- Step 2: Owner login --}}
            @if($currentStep === 2)
                <div class="space-y-4">
                    <p class="text-sm text-base-content/60">Create the login you'll use to manage your storefront.</p>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Full name <span class="text-error">*</span></span></label>
                        <input type="text" wire:model="owner_name" class="input input-bordered w-full @error('owner_name') input-error @enderror" placeholder="Your full name" />
                        @error('owner_name') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email <span class="text-error">*</span></span></label>
                        <input type="email" wire:model="owner_email" class="input input-bordered w-full @error('owner_email') input-error @enderror" placeholder="you@example.com" />
                        @error('owner_email') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Password <span class="text-error">*</span></span></label>
                        <input type="password" wire:model="password" class="input input-bordered w-full @error('password') input-error @enderror" placeholder="Min. 8 characters" />
                        @error('password') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Confirm password <span class="text-error">*</span></span></label>
                        <input type="password" wire:model="password_confirmation" class="input input-bordered w-full" placeholder="Repeat password" />
                    </div>
                </div>
            @endif

            {{-- Step 3: Review --}}
            @if($currentStep === 3)
                <div class="space-y-4">
                    <p class="text-sm text-base-content/60">Review your details before creating your account.</p>

                    <div class="border border-base-300 rounded-lg p-4 space-y-1 text-sm">
                        <h3 class="font-semibold text-primary">Business</h3>
                        <p><span class="text-base-content/60">Name:</span> {{ $business_name }}</p>
                        <p><span class="text-base-content/60">Email:</span> {{ $business_email }}</p>
                        @if($business_phone)<p><span class="text-base-content/60">Phone:</span> {{ $business_phone }}</p>@endif
                        @if($business_address)<p><span class="text-base-content/60">Address:</span> {{ $business_address }}</p>@endif
                    </div>

                    <div class="border border-base-300 rounded-lg p-4 space-y-1 text-sm">
                        <h3 class="font-semibold text-primary">Your Account</h3>
                        <p><span class="text-base-content/60">Name:</span> {{ $owner_name }}</p>
                        <p><span class="text-base-content/60">Email:</span> {{ $owner_email }}</p>
                    </div>

                    <div class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Your account is created instantly, but a platform admin must verify it before you can list products. You'll be able to browse the catalog in the meantime.</span>
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <div class="flex justify-between mt-6">
                @if($currentStep > 1)
                    <button wire:click="previousStep" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Back
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
                    <button wire:click="register" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="register">Create supplier account</span>
                        <span wire:loading wire:target="register" class="loading loading-spinner loading-sm"></span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <p class="text-center text-sm text-base-content/60 mt-4">
        Already have an account?
        <a href="{{ route('login') }}" class="link link-primary font-medium">Sign in</a>
    </p>
    <p class="text-center text-sm text-base-content/60 mt-1">
        Running a garage or car wash instead?
        <a href="{{ route('register') }}" class="link link-primary font-medium">Register here</a>
    </p>
</div>
