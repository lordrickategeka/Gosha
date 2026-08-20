<div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-1">Register Your Garage</h2>
            <p class="text-base-content/60 mb-6">Set up your business and choose a plan to get started</p>

            <!-- Steps -->
            <ul class="steps steps-horizontal w-full mb-6 text-xs">
                <li class="step {{ $currentStep >= 1 ? 'step-primary' : '' }}" wire:click="goToStep(1)">Business</li>
                <li class="step {{ $currentStep >= 2 ? 'step-primary' : '' }}" wire:click="goToStep(2)">Branch</li>
                <li class="step {{ $currentStep >= 3 ? 'step-primary' : '' }}" wire:click="goToStep(3)">Plan</li>
                <li class="step {{ $currentStep >= 4 ? 'step-primary' : '' }}" wire:click="goToStep(4)">Account</li>
                <li class="step {{ $currentStep >= 5 ? 'step-primary' : '' }}">Review</li>
            </ul>

            {{-- Step 1: Business Details --}}
            @if($currentStep === 1)
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Business Name <span class="text-error">*</span></span></label>
                        <input type="text" wire:model="vendor_name" class="input input-bordered w-full @error('vendor_name') input-error @enderror" placeholder="e.g. AutoCare Garage" />
                        @error('vendor_name') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Business Email <span class="text-error">*</span></span></label>
                        <input type="email" wire:model="vendor_email" class="input input-bordered w-full @error('vendor_email') input-error @enderror" placeholder="info@yourbusiness.com" />
                        @error('vendor_email') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Phone</span></label>
                        <input type="text" wire:model="vendor_phone" class="input input-bordered w-full" placeholder="+256 700 000000" />
                        @error('vendor_phone') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Address</span></label>
                        <textarea wire:model="vendor_address" class="textarea textarea-bordered w-full" rows="2" placeholder="Physical address"></textarea>
                        @error('vendor_address') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                </div>
            @endif

            {{-- Step 2: Main Branch --}}
            @if($currentStep === 2)
                <div class="space-y-4">
                    <p class="text-sm text-base-content/60">Set up your primary branch location.</p>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Branch Name <span class="text-error">*</span></span></label>
                        <input type="text" wire:model="branch_name" class="input input-bordered w-full @error('branch_name') input-error @enderror" placeholder="e.g. Main Branch - Kampala" />
                        @error('branch_name') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Branch Email</span></label>
                        <input type="email" wire:model="branch_email" class="input input-bordered w-full" placeholder="branch@yourbusiness.com" />
                        @error('branch_email') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Branch Phone</span></label>
                        <input type="text" wire:model="branch_phone" class="input input-bordered w-full" placeholder="+256 700 000000" />
                        @error('branch_phone') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Branch Address</span></label>
                        <textarea wire:model="branch_address" class="textarea textarea-bordered w-full" rows="2" placeholder="Branch physical address"></textarea>
                        @error('branch_address') <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label> @enderror
                    </div>
                </div>
            @endif

            {{-- Step 3: Choose Plan --}}
            @if($currentStep === 3)
                <div class="space-y-4">
                    <p class="text-sm text-base-content/60">Pick the plan that fits your business. You can change it later.</p>
                    @error('selectedPlanId') <p class="text-error text-sm">{{ $message }}</p> @enderror

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($this->plans as $plan)
                            <div wire:click="selectPlan({{ $plan->id }})"
                                 class="card border-2 cursor-pointer transition {{ $selectedPlanId === $plan->id ? 'border-primary bg-primary/5' : 'border-base-300' }}">
                                <div class="card-body p-4">
                                    <div class="flex items-start justify-between">
                                        <h3 class="font-semibold">{{ $plan->name }}</h3>
                                        @if($plan->is_featured)
                                            <span class="badge badge-primary badge-sm">Popular</span>
                                        @endif
                                    </div>
                                    <p class="text-2xl font-bold">{{ $plan->getFormattedPriceAttribute() }}</p>
                                    @if($plan->description)
                                        <p class="text-xs text-base-content/60">{{ $plan->description }}</p>
                                    @endif
                                    @if($plan->has_trial)
                                        <p class="text-xs text-success font-medium">{{ $plan->trial_days }}-day free trial</p>
                                    @else
                                        <p class="text-xs text-base-content/60">Payment required to activate</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Step 4: Owner Account --}}
            @if($currentStep === 4)
                <div class="space-y-4">
                    <p class="text-sm text-base-content/60">Create your login credentials.</p>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Full Name <span class="text-error">*</span></span></label>
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
                        <label class="label"><span class="label-text font-medium">Confirm Password <span class="text-error">*</span></span></label>
                        <input type="password" wire:model="password_confirmation" class="input input-bordered w-full" placeholder="Repeat password" />
                    </div>
                </div>
            @endif

            {{-- Step 5: Review --}}
            @if($currentStep === 5)
                @php($selectedPlan = $this->plans->firstWhere('id', $selectedPlanId))
                <div class="space-y-4">
                    <p class="text-sm text-base-content/60">Review your details before creating your account.</p>

                    <div class="border border-base-300 rounded-lg p-4 space-y-1 text-sm">
                        <h3 class="font-semibold text-primary">Business</h3>
                        <p><span class="text-base-content/60">Name:</span> {{ $vendor_name }}</p>
                        <p><span class="text-base-content/60">Email:</span> {{ $vendor_email }}</p>
                        @if($vendor_phone)<p><span class="text-base-content/60">Phone:</span> {{ $vendor_phone }}</p>@endif
                    </div>

                    <div class="border border-base-300 rounded-lg p-4 space-y-1 text-sm">
                        <h3 class="font-semibold text-primary">Main Branch</h3>
                        <p><span class="text-base-content/60">Name:</span> {{ $branch_name }}</p>
                        @if($branch_email)<p><span class="text-base-content/60">Email:</span> {{ $branch_email }}</p>@endif
                        @if($branch_phone)<p><span class="text-base-content/60">Phone:</span> {{ $branch_phone }}</p>@endif
                    </div>

                    <div class="border border-base-300 rounded-lg p-4 space-y-1 text-sm">
                        <h3 class="font-semibold text-primary">Your Account</h3>
                        <p><span class="text-base-content/60">Name:</span> {{ $owner_name }}</p>
                        <p><span class="text-base-content/60">Email:</span> {{ $owner_email }}</p>
                    </div>

                    @if($selectedPlan)
                        <div class="border border-base-300 rounded-lg p-4 space-y-1 text-sm">
                            <h3 class="font-semibold text-primary">Plan</h3>
                            <p>{{ $selectedPlan->name }} — {{ $selectedPlan->getFormattedPriceAttribute() }}</p>
                        </div>

                        @if($selectedPlan->has_trial)
                            <div class="alert alert-info">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>You'll get a <strong>{{ $selectedPlan->trial_days }}-day free trial</strong>. No credit card required.</span>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>This plan has no free trial — you'll be asked to pay right after creating your account.</span>
                            </div>
                        @endif
                    @endif
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
                        <span wire:loading.remove wire:target="register">Create Account</span>
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
</div>
