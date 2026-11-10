<div x-data x-on:registration-focus.window="$nextTick(() => $el.querySelector('[aria-invalid=true], [data-step-heading]')?.focus())">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-5 sm:p-8">
            <div class="text-xs uppercase tracking-widest text-primary font-semibold">Start with GarageHQ</div>
            <h1 class="text-2xl sm:text-3xl font-bold">Register your garage</h1>
            <p class="text-sm text-base-content/60">Set up your garage, create your login, and choose a plan. You can add more branches later.</p>

            <nav aria-label="Registration progress" class="my-5">
                <ol class="grid grid-cols-4 gap-2">
                    @foreach([1 => 'Your garage', 2 => 'Your account', 3 => 'Choose a plan', 4 => 'Review'] as $step => $label)
                        <li>
                            <button type="button" wire:click="goToStep({{ $step }})" @disabled($step > $furthestStep)
                                    @if($step === $currentStep) aria-current="step" @endif
                                    class="w-full text-left border-t-4 pt-2 text-xs sm:text-sm {{ $step <= $currentStep ? 'border-primary text-primary' : 'border-base-300 text-base-content/50' }} disabled:cursor-default">
                                <span class="block font-semibold mb-1">{{ $step < $currentStep ? 'Done' : 'Step '.$step }}</span>{{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ol>
            </nav>

            @if($errors->any())
                <div class="alert alert-error text-sm" role="alert" tabindex="-1" data-error-summary>
                    <div><p class="font-semibold">Please check your details.</p><ul class="list-disc pl-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                </div>
            @endif

            <form wire:submit="{{ $currentStep === 4 ? 'register' : 'nextStep' }}" novalidate class="space-y-5">
                <h2 tabindex="-1" data-step-heading class="text-lg font-semibold focus:outline-none">{{ [1 => 'Tell us about your garage', 2 => 'Create your owner account', 3 => 'Choose what fits your garage', 4 => 'Check your details'][$currentStep] }}</h2>

                @if($currentStep === 1)
                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-registration-field name="vendor_name" label="Garage name" required autocomplete="organization" placeholder="e.g. AutoCare Garage" />
                        <x-registration-field name="vendor_email" label="Business email" type="email" required autocomplete="email" hint="Used for business correspondence and billing." placeholder="info@yourgarage.com" />
                        <x-registration-field name="vendor_phone" label="Business phone" type="tel" autocomplete="tel" placeholder="+256 700 000000" />
                        <x-registration-field name="vendor_address" label="Garage location / address" autocomplete="street-address" placeholder="Street, area, and town" />
                    </div>
                    <div class="rounded-xl border border-base-300 p-4 space-y-3">
                        <p class="text-sm">We'll create your <strong>Main Branch</strong> using these business details.</p>
                        <label for="customizeBranch" class="flex items-center gap-3 cursor-pointer text-sm">
                            <input id="customizeBranch" type="checkbox" wire:model.live="customizeBranch" class="checkbox checkbox-primary checkbox-sm" /> Use a different branch name or contact details
                        </label>
                        @if($customizeBranch)
                            <div class="grid sm:grid-cols-2 gap-4">
                                <x-registration-field name="branch_name" label="Branch name" required />
                                <x-registration-field name="branch_email" label="Branch email" type="email" />
                                <x-registration-field name="branch_phone" label="Branch phone" type="tel" />
                                <x-registration-field name="branch_address" label="Branch address" />
                            </div>
                        @endif
                    </div>
                @elseif($currentStep === 2)
                    <p class="text-sm text-base-content/60">You'll be the owner of this garage. Invite your staff after setup.</p>
                    <x-registration-field name="owner_name" label="Your full name" required autocomplete="name" />
                    <label for="useBusinessEmail" class="flex items-center gap-3 cursor-pointer text-sm">
                        <input id="useBusinessEmail" type="checkbox" wire:model.live="useBusinessEmail" class="checkbox checkbox-primary checkbox-sm" /> Use {{ $vendor_email }} as my login email
                    </label>
                    @if(!$useBusinessEmail)
                        <x-registration-field name="owner_email" label="Login email" type="email" required autocomplete="username" />
                    @else
                        @error('owner_email')<p class="text-error text-sm" role="alert">{{ $message }} Uncheck the option above to use a different login email.</p>@enderror
                    @endif
                    <section class="rounded-xl border border-base-300 p-4 space-y-3" aria-label="Verify your login email">
                        @if($this->emailVerified)
                            <p class="text-success font-semibold" role="status">Login email verified</p>
                        @else
                            <h3 class="font-semibold">Verify your login email</h3>
                            <p class="text-sm text-base-content/60">We'll send a six-digit code to {{ $useBusinessEmail ? $vendor_email : ($owner_email ?: 'your login email') }}. Your trial starts after verification and garage creation.</p>
                            <div x-data="{ now: Math.floor(Date.now() / 1000) }" x-init="setInterval(() => now = Math.floor(Date.now() / 1000), 1000)">
                                <button type="button" wire:click="sendVerificationCode" wire:loading.attr="disabled" x-bind:disabled="now < {{ $resendAt }}" class="btn btn-sm btn-outline btn-primary">{{ $resendAt ? 'Resend code' : 'Send verification code' }}</button>
                                <span x-show="now < {{ $resendAt }}" class="text-xs ml-2" x-text="'Resend in ' + Math.max(0, {{ $resendAt }} - now) + 's'"></span>
                            </div>
                            @if($resendAt)
                                <div class="flex flex-wrap gap-3 items-end">
                                    <div><label for="verificationCode" class="block text-sm mb-1">Verification code</label><input id="verificationCode" wire:model="verificationCode" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" class="input input-bordered w-40" /></div>
                                    <button type="button" wire:click="verifyEmail" wire:loading.attr="disabled" class="btn btn-primary">Verify email</button>
                                </div>
                            @endif
                        @endif
                        @error('verification')<p class="text-error text-sm" role="alert">{{ $message }}</p>@enderror
                        @error('verificationCode')<p class="text-error text-sm" role="alert">{{ $message }}</p>@enderror
                    </section>
                    @if($this->smsEnabled)
                        <section class="rounded-xl border border-base-300 p-4 space-y-3">
                            <x-registration-field name="owner_phone" label="Your mobile number" type="tel" hint="Use international format, e.g. +256700000000. Phone verification is optional." />
                            @if(app(\App\Domains\Platform\Services\SignupVerificationService::class)->verified($verificationToken, $owner_phone, 'phone'))
                                <p class="text-success text-sm" role="status">Phone verified</p>
                            @else
                                <button type="button" wire:click="sendPhoneCode" wire:loading.attr="disabled" class="btn btn-outline btn-sm">Send phone code</button>
                                <label for="phoneCode" class="sr-only">Phone verification code</label><input id="phoneCode" wire:model="phoneCode" maxlength="6" inputmode="numeric" autocomplete="one-time-code" class="input input-bordered w-32" placeholder="Code" />
                                <button type="button" wire:click="verifyPhone" wire:loading.attr="disabled" class="btn btn-outline btn-sm">Verify phone</button>
                            @endif
                            @error('phoneCode')<p class="text-error text-sm" role="alert">{{ $message }}</p>@enderror
                        </section>
                    @endif
                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-registration-field name="password" label="Password" type="password" required autocomplete="new-password" hint="Use at least 8 characters." />
                        <x-registration-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" />
                    </div>
                @elseif($currentStep === 3)
                    <p class="text-sm text-base-content/60">Start with 14 days free, or choose a paid subscription. The free trial has no card requirement, setup fee, or automatic charge.</p>
                    <fieldset>
                        <legend class="sr-only">Available garage plans</legend>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @forelse($this->plans as $plan)
                                <label wire:key="plan-{{ $plan->id }}" class="rounded-xl border-2 p-4 space-y-3 cursor-pointer {{ $selectedPlanId === $plan->id ? 'border-primary bg-primary/5' : 'border-base-300' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-semibold">{{ $plan->name }}</span>
                                        <input type="radio" wire:model.live="selectedPlanId" name="selectedPlanId" value="{{ $plan->id }}" @disabled(!$this->canRegisterWith($plan)) class="radio radio-primary radio-sm" />
                                    </div>
                                    @if($plan->is_trial)<span class="badge badge-success badge-sm">Start here · No card required</span>@elseif($plan->is_featured)<span class="badge badge-primary badge-sm">Popular</span>@endif
                                    @include('livewire.auth.partials.plan-costs', ['plan' => $plan])
                                    @if(!$this->canRegisterWith($plan))<p class="text-sm text-warning">Assisted setup required{{ $plan->has_trial && $plan->trial_requires_card ? ': this trial requires a card.' : '.' }} Choose another plan to register online.</p>@endif
                                </label>
                            @empty
                                <p class="sm:col-span-2 alert">There are no plans available right now. Please try again later. Your details are kept while this page stays open.</p>
                            @endforelse
                        </div>
                    </fieldset>
                    @error('selectedPlanId')<p class="text-error text-sm" role="alert">{{ $message }}</p>@enderror
                    <div class="rounded-xl bg-base-200 p-4 text-sm space-y-2">
                        <h3 class="font-semibold">Tools available in GarageHQ</h3>
                        <p>Repair work orders and job cards · Wash operations · Customer and vehicle history · Inventory and parts · Staff and commissions · Expenses and reports</p>
                        <p class="text-base-content/60">Your team's roles control access to individual tools. Trial access ends after 14 days; your data stays safe while you choose a subscription.</p>
                    </div>
                @else
                    @php($plan = $this->selectedPlan)
                    <p class="text-sm text-base-content/60">Make sure everything looks right. Your garage is created when you submit below.</p>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach([
                            ['Your garage', 1, ['Name' => $vendor_name, 'Business email' => $vendor_email, 'Phone' => $vendor_phone, 'Address' => $vendor_address]],
                            ['Main branch', 1, ['Name' => $customizeBranch ? $branch_name : 'Main Branch', 'Email' => $customizeBranch ? $branch_email : $vendor_email, 'Phone' => $customizeBranch ? $branch_phone : $vendor_phone, 'Address' => $customizeBranch ? $branch_address : $vendor_address]],
                            ['Owner account', 2, ['Name' => $owner_name, 'Login email' => $useBusinessEmail ? $vendor_email : $owner_email]],
                        ] as [$heading, $editStep, $details])
                            <section class="rounded-xl border border-base-300 p-4">
                                <div class="flex justify-between gap-3 mb-3"><h3 class="font-semibold">{{ $heading }}</h3><button type="button" wire:click="goToStep({{ $editStep }})" class="link link-primary text-sm" aria-label="Edit {{ strtolower($heading) }}">Edit</button></div>
                                <dl class="text-sm space-y-2 break-words">@foreach($details as $label => $value)<div><dt class="text-base-content/60">{{ $label }}</dt><dd>{{ $value ?: 'Not provided' }}</dd></div>@endforeach</dl>
                            </section>
                        @endforeach
                        <section class="rounded-xl border border-base-300 p-4 space-y-3">
                            <div class="flex justify-between gap-3"><h3 class="font-semibold">{{ $plan?->name ?? 'Choose a plan' }}</h3><button type="button" wire:click="goToStep(3)" class="link link-primary text-sm" aria-label="Edit plan">Edit</button></div>
                            @if($plan)
                                @include('livewire.auth.partials.plan-costs', ['plan' => $plan])
                                <p class="border-t border-base-300 pt-3 font-semibold">Due now: {{ $plan->currency }} {{ number_format($this->dueNow, 2) }}</p>
                                <p class="text-xs text-base-content/60">{{ $plan->is_trial ? 'No payment, card or setup fee required.' : 'Includes applicable platform tax and any setup fee. Payment is completed securely after account creation.' }}</p>
                            @endif
                        </section>
                    </div>
                    <div class="rounded-xl bg-base-200 p-4 text-sm">
                        @if($plan?->is_trial)
                            Your 14-day trial starts when you create your garage. No card is required for this trial. We'll email your expiry date and reminders. After 14 days, choose and pay for a subscription to continue. No automatic charge.
                        @endif
                        @if($this->dueNow > 0 || $plan?->setup_fee > 0)
                            Next, we'll take you to billing to review and pay your invoices.
                        @else
                            Next, we'll take you to your dashboard to set up services, invite staff, and add your first customer.
                        @endif
                    </div>
                @endif

                <div class="flex justify-between gap-3 border-t border-base-300 pt-5">
                    @if($currentStep > 1)<button type="button" wire:click="previousStep" wire:loading.attr="disabled" class="btn btn-ghost">Back</button>@else<div></div>@endif
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="nextStep,register">{{ $currentStep === 4 ? 'Create my garage' : 'Continue' }}</span>
                        <span wire:loading wire:target="nextStep,register" role="status">{{ $currentStep === 4 ? 'Creating your garage…' : 'Checking details…' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <p class="text-center text-sm text-base-content/60 mt-5">Already have an account? <a href="{{ route('login') }}" class="link link-primary font-medium">Sign in</a></p>
    <p class="text-center text-sm text-base-content/60 mt-2">Selling parts? <a href="{{ route('supplier.register') }}" class="link link-primary">Register as a supplier</a></p>
</div>
