<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Registration;

use App\Domains\Marketplace\Notifications\SupplierSignupReceived;
use App\Domains\Platform\Models\Vendor;
use App\Domains\Platform\Services\SignupVerificationService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.guest', ['wide' => true, 'supplier' => true, 'title' => 'Supplier registration | GarageHQ'])]
class Register extends Component
{
    #[Locked]
    public int $currentStep = 1;

    #[Locked]
    public int $furthestStep = 1;

    #[Locked]
    public string $verificationToken = '';

    #[Locked]
    public int $resendAt = 0;

    public string $business_name = '';

    public string $business_email = '';

    public string $business_phone = '';

    public string $business_address = '';

    public bool $useBusinessEmail = true;

    public string $owner_name = '';

    public string $owner_email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $verificationCode = '';

    public ?int $selectedPlanId = null;

    public function getPlansProperty()
    {
        return \App\Domains\Platform\Models\PricingPlan::active()->where('audience', 'supplier')->orderByDesc('is_trial')->orderBy('sort_order')->get();
    }

    public function mount(): void
    {
        $this->verificationToken = (string) Str::uuid();
        $this->selectedPlanId = $this->plans->firstWhere('is_trial', true)?->id ?? $this->plans->first()?->id;
    }

    public function getEmailVerifiedProperty(): bool
    {
        return app(SignupVerificationService::class)->verified($this->verificationToken,
            mb_strtolower(trim($this->useBusinessEmail ? $this->business_email : $this->owner_email)));
    }

    protected function normalizeDetails(): void
    {
        foreach (['business_name', 'business_phone', 'business_address', 'owner_name'] as $field) {
            $this->$field = trim($this->$field);
        }
        $this->business_email = mb_strtolower(trim($this->business_email));
        $this->owner_email = $this->useBusinessEmail ? $this->business_email : mb_strtolower(trim($this->owner_email));
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

    public function nextStep(): void
    {
        $this->dispatch('supplier-registration-focus');
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
        if ($step >= 1 && $step <= 4 && $step <= $this->furthestStep) {
            $this->currentStep = $step;
            $this->resetValidation();
            $this->dispatch('supplier-registration-focus');
        }
    }

    protected function validateStep(int $step): void
    {
        $this->validate(match ($step) {
            1 => [
                'business_name' => 'required|string|max:255',
                'business_email' => 'required|email|max:255|unique:vendors,email',
                'business_phone' => 'nullable|string|max:50',
                'business_address' => 'nullable|string|max:500',
            ],
            2 => [
                'owner_name' => 'required|string|max:255',
                'owner_email' => 'required|email|max:255|unique:users,email',
                'password' => ['required', 'string', 'max:255', Password::defaults(), 'confirmed'],
            ],
            3 => ['selectedPlanId' => ['required', 'integer', \Illuminate\Validation\Rule::exists('pricing_plans', 'id')->where('is_active', true)->where('audience', 'supplier')->whereNull('deleted_at')]],
            default => [],
        }, ['owner_email.unique' => 'This login email already has an account. Sign in or use another email.'], [
            'business_name' => 'business name', 'business_email' => 'business email',
            'owner_name' => 'full name', 'owner_email' => 'login email',
        ]);
        if ($step === 2 && ! $this->emailVerified) {
            throw ValidationException::withMessages(['verification' => 'Verify your login email before continuing.']);
        }
        if ($step === 3 && ! app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class)->available($this->plans->firstWhere('id', $this->selectedPlanId))) {
            throw ValidationException::withMessages(['selectedPlanId' => 'Choose an available supplier subscription plan.']);
        }
    }

    public function register()
    {
        $this->dispatch('supplier-registration-focus');
        if (Auth::check()) {
            return redirect()->route(Auth::user()->hasRole('supplier_admin') ? 'supplier.dashboard' : 'dashboard');
        }
        $rateKey = 'supplier-registration:'.hash('sha256', request()->ip());
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $this->addError('registration', 'Too many registration attempts. Please try again in a minute.');

            return;
        }
        RateLimiter::hit($rateKey, 60);
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
        // Shared with garage registration so simultaneous signups cannot reuse one login.
        $lock = Cache::lock('garage-registration:'.hash('sha256', $this->owner_email), 30);
        if (! $lock->get()) {
            $this->addError('registration', 'Your registration is already being processed. Please wait a moment.');

            return;
        }
        try {
            $owner = DB::transaction(function () {
                $this->validateStep(1);
                $this->validateStep(2);
                $this->validateStep(3);
                $vendor = Vendor::create([
                    'slug' => Str::limit(Str::slug($this->business_name) ?: 'supplier', 200, '').'-'.Str::lower(Str::random(10)),
                    'name' => $this->business_name, 'email' => $this->business_email,
                    'phone' => $this->business_phone ?: null, 'address' => $this->business_address ?: null,
                    'status' => 'active', 'vendor_type' => 'supplier', 'is_verified_supplier' => false,
                ]);
                $owner = User::create([
                    'vendor_id' => $vendor->id, 'name' => $this->owner_name, 'email' => $this->owner_email,
                    'password' => Hash::make($this->password), 'is_active' => true,
                ]);
                $owner->forceFill(['email_verified_at' => now()])->save();
                $owner->assignRole('supplier_admin');
                app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class)->start($vendor, \App\Domains\Platform\Models\PricingPlan::findOrFail($this->selectedPlanId));
                // Durable queued delivery; email availability does not decide signup success.
                $owner->notify(new SupplierSignupReceived);

                return $owner;
            });
        } catch (ValidationException $exception) {
            $fields = array_keys($exception->errors());
            $this->currentStep = in_array('selectedPlanId', $fields) ? 3 : (array_intersect($fields, ['owner_name', 'owner_email', 'password', 'verification']) ? 2 : 1);
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('registration', 'We could not create your supplier account. Your details are still here; please try again.');

            return;
        } finally {
            $lock->release();
        }
        app(SignupVerificationService::class)->forget($this->verificationToken);
        // A supplier has no garage branch; discard any selection left by an earlier login.
        session()->forget(['current_branch_id', 'current_branch_name', 'current_vendor_id', 'garage_setup']);
        Auth::login($owner);
        session()->regenerate();
        $this->reset('password', 'password_confirmation');
        session()->flash('success', 'Your email is verified and your supplier account is ready. Platform approval is pending; you can browse the catalog while we review your business.');

        return redirect()->route('supplier.dashboard');
    }

    public function render()
    {
        return view('livewire.marketplace.supplier.registration.register');
    }
}
