<?php

namespace App\Domains\HR\Livewire\Auth;

use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Setting;
use App\Domains\Platform\Models\PlatformSetting;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use App\Domains\Platform\Services\SignupVerificationService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.guest', ['wide' => true])]
class VendorRegistration extends Component
{
    #[Locked]
    public int $currentStep = 1;

    #[Locked]
    public int $furthestStep = 1;

    public string $vendor_name = '';

    public string $vendor_email = '';

    public string $vendor_phone = '';

    public string $vendor_address = '';

    public bool $customizeBranch = false;

    public string $branch_name = 'Main Branch';

    public string $branch_address = '';

    public string $branch_phone = '';

    public string $branch_email = '';

    public bool $useBusinessEmail = true;

    public string $owner_name = '';

    public string $owner_email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $selectedPlanId = null;

    #[Locked]
    public string $verificationToken = '';

    #[Locked]
    public int $resendAt = 0;

    public string $verificationCode = '';

    public string $owner_phone = '';

    public string $phoneCode = '';

    public function getEmailVerifiedProperty(): bool
    {
        $email = mb_strtolower(trim($this->useBusinessEmail ? $this->vendor_email : $this->owner_email));

        return app(SignupVerificationService::class)->verified($this->verificationToken, $email);
    }

    public function getSmsEnabledProperty(): bool
    {
        $integration = app(\App\Domains\Platform\Services\ApiIntegrationService::class)->getIntegration('onepass');

        return $integration?->isActive() && ($integration->credentials['sms_enabled'] ?? '') === 'true';
    }

    public function sendVerificationCode(): void
    {
        $this->normalizeDetails();
        $this->validate(['owner_email' => 'required|email|max:255|unique:users,email']);
        try {
            app(SignupVerificationService::class)->request($this->verificationToken, $this->owner_email);
        } catch (ValidationException $exception) {
            $this->resendAt = now()->timestamp + RateLimiter::availableIn('signup-send:'.hash('sha256', $this->owner_email));
            throw $exception;
        }
        $this->resendAt = now()->addMinute()->timestamp;
        $this->verificationCode = '';
        $this->resetValidation('verification');
    }

    public function verifyEmail(): void
    {
        $this->normalizeDetails();
        $this->validate(['verificationCode' => 'required|digits:6']);
        app(SignupVerificationService::class)->verify($this->verificationToken, $this->owner_email, $this->verificationCode);
        $this->verificationCode = '';
        $this->resetValidation('verification');
    }

    public function sendPhoneCode(): void
    {
        abort_unless($this->smsEnabled, 403);
        $this->validate(['owner_phone' => ['required', 'regex:/^\+[1-9][0-9]{7,14}$/']]);
        app(SignupVerificationService::class)->request($this->verificationToken, $this->owner_phone, 'phone');
    }

    public function verifyPhone(): void
    {
        abort_unless($this->smsEnabled, 403);
        $this->validate(['phoneCode' => 'required|digits:6']);
        app(SignupVerificationService::class)->verify($this->verificationToken, $this->owner_phone, $this->phoneCode, 'phone');
        $this->phoneCode = '';
    }

    public function mount(): void
    {
        $this->verificationToken = (string) Str::uuid();
        $plans = $this->plans->filter(fn ($plan) => $this->canRegisterWith($plan));
        $this->selectedPlanId = ($plans->firstWhere('is_trial', true) ?? $plans->firstWhere('is_default', true) ?? $plans->first())?->id;
    }

    public function getPlansProperty()
    {
        return PricingPlan::active()->where('audience', 'garage')->orderByDesc('is_trial')->orderBy('sort_order')->orderBy('base_price')->get();
    }

    public function canRegisterWith(PricingPlan $plan): bool
    {
        return $plan->audience === 'garage' && app(SelfServiceSubscriptionService::class)->available($plan);
    }

    public function getSelectedPlanProperty(): ?PricingPlan
    {
        return $this->plans->firstWhere('id', $this->selectedPlanId);
    }

    public function getDueNowProperty(): float
    {
        $plan = $this->selectedPlan;
        if (! $plan) {
            return 0;
        }
        $base = ! $plan->is_trial && $plan->hasSubscriptionComponent() ? (float) $plan->base_price : 0;
        $tax = (float) PlatformSetting::get(PlatformSetting::PLATFORM_TAX_RATE, 0);

        // Trial signup is always zero; paid signup includes the first subscription and setup fee.
        return $plan->is_trial ? 0 : round($base * (1 + $tax / 100) + (float) $plan->setup_fee, 2);
    }

    public function selectPlan(int $planId): void
    {
        $this->selectedPlanId = $planId;
        $this->validateStep(3);
    }

    public function nextStep(): void
    {
        $this->dispatch('registration-focus');
        $this->normalizeDetails();
        $this->validateStep($this->currentStep);
        $this->currentStep = min(4, $this->currentStep + 1);
        $this->furthestStep = max($this->furthestStep, $this->currentStep);
        $this->resetValidation();
    }

    public function previousStep(): void
    {
        $this->goToStep($this->currentStep - 1);
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->furthestStep && $step <= 4) {
            $this->currentStep = $step;
            $this->resetValidation();
            $this->dispatch('registration-focus');
        }
    }

    protected function normalizeDetails(): void
    {
        foreach (['vendor_name', 'vendor_phone', 'vendor_address', 'branch_name', 'branch_phone', 'branch_address', 'owner_name'] as $field) {
            $this->$field = trim($this->$field);
        }
        foreach (['vendor_email', 'branch_email', 'owner_email'] as $field) {
            $this->$field = mb_strtolower(trim($this->$field));
        }
        if ($this->useBusinessEmail) {
            $this->owner_email = $this->vendor_email;
        }
    }

    protected function validateStep(int $step): void
    {
        $rules = match ($step) {
            1 => [
                'vendor_name' => ['required', 'string', 'max:255'],
                'vendor_email' => ['required', 'email', 'max:255', 'unique:vendors,email'],
                'vendor_phone' => ['nullable', 'string', 'max:50'],
                'vendor_address' => ['nullable', 'string', 'max:500'],
                ...($this->customizeBranch ? [
                    'branch_name' => ['required', 'string', 'max:255'],
                    'branch_email' => ['nullable', 'email', 'max:255'],
                    'branch_phone' => ['nullable', 'string', 'max:50'],
                    'branch_address' => ['nullable', 'string', 'max:500'],
                ] : []),
            ],
            2 => [
                'owner_name' => ['required', 'string', 'max:255'],
                'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'max:255', Password::defaults(), 'confirmed'],
                'owner_phone' => ['nullable', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            ],
            3 => [
                'selectedPlanId' => ['required', 'integer', Rule::exists('pricing_plans', 'id')->where('is_active', true)->whereNull('deleted_at')],
            ],
            default => [],
        };
        $this->validate($rules, [
            'owner_email.unique' => 'This login email already has an account. Sign in or use another email.',
            'selectedPlanId.required' => 'Choose an available plan to continue.',
            'selectedPlanId.exists' => 'This plan is no longer available. Please choose another plan.',
        ], [
            'vendor_name' => 'garage name', 'vendor_email' => 'business email',
            'owner_name' => 'full name', 'owner_email' => 'login email', 'selectedPlanId' => 'plan',
        ]);
        if ($step === 2 && ! $this->emailVerified) {
            throw ValidationException::withMessages(['verification' => 'Verify your login email before continuing.']);
        }
        if ($step === 3 && ! $this->canRegisterWith($this->selectedPlan)) {
            throw ValidationException::withMessages(['selectedPlanId' => 'This plan needs assisted setup. Please choose another plan for online registration.']);
        }
    }

    public function register()
    {
        $this->dispatch('registration-focus');
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $key = 'garage-registration:'.hash('sha256', request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('registration', 'Too many registration attempts. Please try again in a minute.');

            return;
        }
        RateLimiter::hit($key, 60);
        $this->normalizeDetails();
        foreach ([1, 2, 3] as $step) {
            try {
                $this->validateStep($step);
            } catch (ValidationException $exception) {
                $this->currentStep = $step;
                throw $exception;
            }
        }
        $this->currentStep = 4;
        $lock = Cache::lock('garage-registration:'.hash('sha256', $this->owner_email), 30);
        if (! $lock->get()) {
            $this->addError('registration', 'Your registration is already being processed. Please wait a moment.');

            return;
        }
        try {
            [$owner, $branch, $subscription] = DB::transaction(function () {
                $this->validateStep(1);
                $this->validateStep(2);
                $this->validateStep(3);
                $plan = PricingPlan::active()->lockForUpdate()->findOrFail($this->selectedPlanId);
                if (! $this->canRegisterWith($plan)) {
                    throw ValidationException::withMessages(['selectedPlanId' => 'Please choose another available plan.']);
                }
                $vendor = Vendor::create([
                    // Different garages can share a business name; their workspace slugs must differ.
                    'slug' => Str::limit(Str::slug($this->vendor_name) ?: 'garage', 200, '').'-'.Str::lower(Str::random(10)),
                    'name' => $this->vendor_name, 'email' => $this->vendor_email,
                    'phone' => $this->vendor_phone, 'address' => $this->vendor_address, 'status' => 'trial',
                ]);
                Setting::createDefaultsForVendor($vendor->id);
                $branch = Branch::create([
                    'vendor_id' => $vendor->id,
                    'name' => $this->customizeBranch ? $this->branch_name : 'Main Branch',
                    'email' => $this->customizeBranch ? $this->branch_email : $this->vendor_email,
                    'phone' => $this->customizeBranch ? $this->branch_phone : $this->vendor_phone,
                    'address' => $this->customizeBranch ? $this->branch_address : $this->vendor_address,
                    'is_active' => true, 'is_main' => true,
                ]);
                $owner = User::create([
                    'vendor_id' => $vendor->id, 'name' => $this->owner_name,
                    'email' => $this->owner_email, 'password' => Hash::make($this->password), 'is_active' => true,
                ]);
                $owner->assignRole('vendor-owner');
                $owner->forceFill([
                    'email_verified_at' => now(), 'phone' => $this->owner_phone ?: null,
                    'phone_verified_at' => app(SignupVerificationService::class)->verified($this->verificationToken, $this->owner_phone, 'phone') ? now() : null,
                ])->save();
                $owner->branches()->attach($branch->id, ['is_primary' => true]);
                $subscription = app(SelfServiceSubscriptionService::class)->start($vendor, $plan);
                $vendor->update([
                    'status' => $subscription->isTrialing() ? 'trial' : 'active',
                    'trial_ends_at' => $subscription->trial_ends_at,
                ]);
                if ($subscription->isTrialing()) {
                    app(SelfServiceSubscriptionService::class)->notice($subscription, 'trial-started', 'Your 14-day GarageHQ trial has started',
                        'Welcome! Your free trial ends on '.$subscription->trial_ends_at->format('d M Y H:i T').'. No card is required and no automatic charge will be taken. Choose a paid subscription in Billing to continue after your trial.');
                }

                return [$owner, $branch, $subscription];
            });
        } catch (ValidationException $exception) {
            $fields = array_keys($exception->errors());
            $this->currentStep = in_array('selectedPlanId', $fields) ? 3 : (in_array('owner_email', $fields) ? 2 : 1);
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('registration', 'We could not create your garage. Your details are still here; please try again.');

            return;
        } finally {
            $lock->release();
        }
        app(SignupVerificationService::class)->forget($this->verificationToken);
        Auth::login($owner);
        session()->regenerate();
        session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name, 'garage_setup' => true]);
        $this->reset('password', 'password_confirmation');
        $hasInvoice = $subscription->invoices()->where('balance_due', '>', 0)->exists();
        $message = 'Welcome! Your garage and main branch are ready.';
        if ($subscription->isTrialing()) {
            $message .= ' Your trial ends on '.$subscription->trial_ends_at->format('d M Y').'.';
        }
        if ($hasInvoice) {
            $message .= ' Please review your invoice below, including its payment due date.';
        }
        session()->flash('success', $message);

        return redirect()->route($hasInvoice ? 'billing.subscription' : 'dashboard');
    }

    public function render()
    {
        return view('livewire.auth.vendor-registration');
    }
}
