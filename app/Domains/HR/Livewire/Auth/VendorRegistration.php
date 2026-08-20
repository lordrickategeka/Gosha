<?php

namespace App\Domains\HR\Livewire\Auth;

use App\Domains\Finance\Services\BillingService;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Setting;
use App\Models\User;
use App\Domains\Platform\Models\PricingPlan;
use App\Domains\Platform\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class VendorRegistration extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 5;

    // Step 1: Business Details
    public $vendor_name = '';
    public $vendor_email = '';
    public $vendor_phone = '';
    public $vendor_address = '';

    // Step 2: Main Branch
    public $branch_name = '';
    public $branch_address = '';
    public $branch_phone = '';
    public $branch_email = '';

    // Step 3: Choose Plan
    public $selectedPlanId = null;

    // Step 4: Owner Account
    public $owner_name = '';
    public $owner_email = '';
    public $password = '';
    public $password_confirmation = '';

    public function mount()
    {
        $this->selectedPlanId = PricingPlan::where('is_active', true)->where('is_default', true)->value('id')
            ?? PricingPlan::where('is_active', true)->orderBy('sort_order')->value('id');
    }

    public function getPlansProperty()
    {
        return PricingPlan::where('is_active', true)->orderBy('sort_order')->orderBy('base_price')->get();
    }

    public function selectPlan(int $planId)
    {
        $this->selectedPlanId = $planId;
    }

    public function nextStep()
    {
        $this->validateCurrentStep();
        $this->currentStep = min($this->currentStep + 1, $this->totalSteps);
    }

    public function previousStep()
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step)
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    protected function validateCurrentStep()
    {
        match ($this->currentStep) {
            1 => $this->validate([
                'vendor_name' => 'required|string|max:255',
                'vendor_email' => 'required|email|unique:vendors,email',
                'vendor_phone' => 'nullable|string|max:50',
                'vendor_address' => 'nullable|string|max:500',
            ]),
            2 => $this->validate([
                'branch_name' => 'required|string|max:255',
                'branch_address' => 'nullable|string|max:500',
                'branch_phone' => 'nullable|string|max:50',
                'branch_email' => 'nullable|email|max:255',
            ]),
            3 => $this->validate([
                'selectedPlanId' => 'required|exists:pricing_plans,id',
            ]),
            4 => $this->validate([
                'owner_name' => 'required|string|max:255',
                'owner_email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]),
            default => null,
        };
    }

    public function register()
    {
        // Re-validate all steps
        $this->currentStep = 1;
        $this->validateCurrentStep();
        $this->currentStep = 2;
        $this->validateCurrentStep();
        $this->currentStep = 3;
        $this->validateCurrentStep();
        $this->currentStep = 4;
        $this->validateCurrentStep();
        $this->currentStep = 5;

        $plan = PricingPlan::findOrFail($this->selectedPlanId);
        $owner = null;
        $subscription = null;

        DB::transaction(function () use (&$owner, &$subscription, $plan) {
            // 1. Create Vendor (status stays "trial" until the subscription
            // says otherwise — the subscription is the source of truth for
            // billing state, this flag is only used for admin manual suspend)
            $vendor = Vendor::create([
                'name' => $this->vendor_name,
                'email' => $this->vendor_email,
                'phone' => $this->vendor_phone,
                'address' => $this->vendor_address,
                'status' => 'trial',
            ]);

            // 2. Create Default Settings
            Setting::createDefaultsForVendor($vendor->id);

            // 3. Create Main Branch
            $branch = Branch::create([
                'vendor_id' => $vendor->id,
                'name' => $this->branch_name,
                'address' => $this->branch_address,
                'phone' => $this->branch_phone,
                'email' => $this->branch_email,
                'is_active' => true,
                'is_main' => true,
            ]);

            // 4. Create Owner User
            $owner = User::create([
                'vendor_id' => $vendor->id,
                'name' => $this->owner_name,
                'email' => $this->owner_email,
                'password' => Hash::make($this->password),
                'is_active' => true,
            ]);

            $owner->assignRole('vendor-owner');
            $owner->branches()->attach($branch->id, ['is_primary' => true]);

            // 5. Subscribe to the chosen plan (trial or billed immediately,
            // handled by BillingService::createSubscription)
            $subscription = app(BillingService::class)->createSubscription($vendor, $plan);

            if ($subscription->isTrialing()) {
                $vendor->update(['status' => 'trial', 'trial_ends_at' => $subscription->trial_ends_at]);
            } else {
                $vendor->update(['status' => 'active']);
            }
        });

        // Auto-login
        Auth::login($owner);

        // Set branch session
        $branch = $owner->primaryBranch();
        if ($branch) {
            session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
        }

        session()->regenerate();

        if ($subscription->isTrialing()) {
            session()->flash('success', "Welcome! Your account has been created. You have a {$plan->trial_days}-day free trial.");

            return redirect()->route('dashboard');
        }

        session()->flash('success', 'Welcome! Your account has been created. Complete payment below to activate your subscription.');

        return redirect()->route('billing.subscription');
    }

    public function render()
    {
        return view('livewire.auth.vendor-registration');
    }
}
