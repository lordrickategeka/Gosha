<div x-data x-on:supplier-registration-focus.window="$nextTick(() => $el.querySelector('[aria-invalid=true], [data-step-heading]')?.focus())">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-5 sm:p-8">
            <div class="text-xs uppercase tracking-widest text-primary font-semibold">GarageHQ Marketplace</div>
            <h1 class="text-2xl sm:text-3xl font-bold">Register as a supplier</h1>
            <p class="text-sm text-base-content/60">Create your business profile and a verified login to get started selling parts.</p>

            <nav aria-label="Supplier registration progress" class="my-5">
                <ol class="grid grid-cols-4 gap-3">
                    @foreach([1 => 'Your business', 2 => 'Your account', 3 => 'Your plan', 4 => 'Review'] as $step => $label)
                        <li><button type="button" wire:click="goToStep({{ $step }})" @disabled($step > $furthestStep)
                            @if($step === $currentStep) aria-current="step" @endif
                            class="w-full text-left border-t-4 pt-2 text-xs sm:text-sm {{ $step <= $currentStep ? 'border-primary text-primary' : 'border-base-300 text-base-content/50' }} disabled:cursor-default">
                            <span class="block font-semibold mb-1">{{ $step < $currentStep ? 'Done' : 'Step '.$step }}</span>{{ $label }}
                        </button></li>
                    @endforeach
                </ol>
            </nav>

            @if($errors->any())
                <div class="alert alert-error text-sm" role="alert"><div><p class="font-semibold">Please check your details.</p><ul class="list-disc pl-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif

            <form wire:submit="{{ $currentStep === 4 ? 'register' : 'nextStep' }}" novalidate class="space-y-5">
                <h2 tabindex="-1" data-step-heading class="text-lg font-semibold focus:outline-none">{{ [1 => 'Tell us about your business', 2 => 'Create your supplier login', 3 => 'Choose your supplier plan', 4 => 'Check your details'][$currentStep] }}</h2>

                @if($currentStep === 1)
                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-registration-field name="business_name" label="Business name" required autocomplete="organization" placeholder="e.g. Kampala Auto Parts Ltd" />
                        <x-registration-field name="business_email" label="Business email" type="email" required autocomplete="email" hint="Used for business correspondence." placeholder="sales@yourbusiness.com" />
                        <x-registration-field name="business_phone" label="Business phone" type="tel" autocomplete="tel" placeholder="+256 700 000000" />
                    </div>
                    <div class="form-control">
                        <label for="business_address" class="label justify-start gap-1 font-medium">Business / warehouse address <span class="text-xs font-normal text-base-content/60">(optional)</span></label>
                        <textarea id="business_address" wire:model="business_address" maxlength="500" autocomplete="street-address" rows="2" class="textarea textarea-bordered w-full @error('business_address') textarea-error @enderror" aria-invalid="{{ $errors->has('business_address') ? 'true' : 'false' }}" aria-describedby="business_address-feedback" placeholder="Street, area, and town"></textarea>
                        <div id="business_address-feedback" class="text-xs mt-1">@error('business_address')<span class="text-error" role="alert">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="rounded-xl bg-base-200 p-4 text-sm space-y-2">
                        <h3 class="font-semibold">What happens next</h3>
                        <p>Verify your login email, then submit your registration. You can browse the catalog while a platform administrator reviews your business. Approval unlocks listings and product submissions.</p>
                    </div>
                @elseif($currentStep === 2)
                    <p class="text-sm text-base-content/60">This account will manage your supplier business. Email verification and business approval are separate steps.</p>
                    <x-registration-field name="owner_name" label="Your full name" required autocomplete="name" />
                    <label for="useBusinessEmail" class="flex items-center gap-3 cursor-pointer text-sm">
                        <input id="useBusinessEmail" type="checkbox" wire:model.live="useBusinessEmail" class="checkbox checkbox-primary checkbox-sm" /> Use {{ $business_email }} as my login email
                    </label>
                    @if(!$useBusinessEmail)
                        <x-registration-field name="owner_email" label="Login email" type="email" required autocomplete="username" />
                    @else
                        @error('owner_email')<p class="text-error text-sm" role="alert">{{ $message }} Uncheck the option above to use a different login email.</p>@enderror
                    @endif
                    <x-signup-email-verification :email="$useBusinessEmail ? $business_email : $owner_email" :verified="$this->emailVerified" :resend-at="$resendAt" />
                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-registration-field name="password" label="Password" type="password" required autocomplete="new-password" hint="Use at least 8 characters." />
                        <x-registration-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />
                    </div>
                @elseif($currentStep === 3)
                    <p class="text-sm text-base-content/60">Your trial starts after platform approval. Paid plans are invoiced after approval and activate only after payment is verified.</p>
                    <fieldset><legend class="sr-only">Supplier plans</legend><div class="grid sm:grid-cols-2 gap-4">
                        @forelse($this->plans as $plan)
                            <label wire:key="supplier-plan-{{ $plan->id }}" class="rounded-xl border-2 p-4 space-y-3 cursor-pointer {{ $selectedPlanId === $plan->id ? 'border-primary bg-primary/5' : 'border-base-300' }}">
                                <div class="flex justify-between gap-3"><span class="font-semibold">{{ $plan->name }}</span><input type="radio" name="selectedPlanId" wire:model.live="selectedPlanId" value="{{ $plan->id }}" class="radio radio-primary radio-sm" /></div>
                                @if($plan->is_featured)<span class="badge badge-primary badge-sm">Popular</span>@endif
                                @include('livewire.auth.partials.plan-costs', ['plan' => $plan])
                            </label>
                        @empty<p>No supplier plans are available. Please try again later.</p>@endforelse
                    </div></fieldset>
                    @error('selectedPlanId')<p class="text-error text-sm" role="alert">{{ $message }}</p>@enderror
                    <p class="text-sm text-base-content/60">All plans include the catalog, stock and pricing, quotes, and order management. At expiry public listings pause and new selling requires renewal; existing orders remain accessible.</p>
                @else
                    <p class="text-sm text-base-content/60">Your supplier business is created when you submit below. You can edit your details first.</p>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach([
                            ['Your business', 1, ['Name' => $business_name, 'Business email' => $business_email, 'Phone' => $business_phone, 'Address' => $business_address]],
                            ['Your account', 2, ['Name' => $owner_name, 'Login email' => $useBusinessEmail ? $business_email : $owner_email, 'Email status' => $this->emailVerified ? 'Verified' : 'Verification required']],
                        ] as [$heading, $editStep, $details])
                            <section class="rounded-xl border border-base-300 p-4">
                                <div class="flex justify-between gap-3 mb-3"><h3 class="font-semibold">{{ $heading }}</h3><button type="button" wire:click="goToStep({{ $editStep }})" class="link link-primary text-sm" aria-label="Edit {{ strtolower($heading) }}">Edit</button></div>
                                <dl class="text-sm space-y-2 break-words">@foreach($details as $label => $value)<div><dt class="text-base-content/60">{{ $label }}</dt><dd>{{ $value ?: 'Not provided' }}</dd></div>@endforeach</dl>
                            </section>
                        @endforeach
                    </div>
                    @if($selectedPlan = $this->plans->firstWhere('id', $selectedPlanId))
                        <section class="rounded-xl border border-base-300 p-4 space-y-3">
                            <div class="flex justify-between"><h3 class="font-semibold">{{ $selectedPlan->name }}</h3><button type="button" wire:click="goToStep(3)" class="link link-primary text-sm">Edit plan</button></div>
                            @include('livewire.auth.partials.plan-costs', ['plan' => $selectedPlan])
                            <p class="font-medium">Due at registration: UGX 0</p>
                            <p class="text-sm text-base-content/60">{{ $selectedPlan->is_trial ? 'The 14-day clock starts only after your business is approved.' : 'Your subscription invoice is issued after approval. Selling activates after payment confirmation.' }}</p>
                        </section>
                    @endif
                    <section class="rounded-xl bg-base-200 p-4 text-sm space-y-2">
                        <h3 class="font-semibold">Your account starts with platform approval pending</h3>
                        <p>We'll sign you in to your supplier dashboard. Browse the catalog and prepare your product details while your business is reviewed. A platform administrator must approve your business before you can create listings or submit products.</p>
                        <p class="text-base-content/60">Your supplier plan is separate from garage subscriptions. No automatic charge is enabled.</p>
                    </section>
                @endif

                <div class="flex justify-between gap-3 border-t border-base-300 pt-5">
                    @if($currentStep > 1)<button type="button" wire:click="previousStep" wire:loading.attr="disabled" class="btn btn-ghost">Back</button>@else<div></div>@endif
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="nextStep,register">{{ $currentStep === 4 ? 'Create supplier account' : 'Continue' }}</span>
                        <span wire:loading wire:target="nextStep,register" role="status">{{ $currentStep === 4 ? 'Creating your account…' : 'Checking details…' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <p class="text-center text-sm text-base-content/60 mt-5">Already have an account? <a href="{{ route('login') }}" class="link link-primary font-medium">Sign in</a></p>
    <p class="text-center text-sm text-base-content/60 mt-2">Running a garage or car wash? <a href="{{ route('register') }}" class="link link-primary">Register your garage</a></p>
</div>
