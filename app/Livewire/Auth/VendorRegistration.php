<?php

namespace App\Livewire\Auth;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBillingConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class VendorRegistration extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 4;

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

    // Step 3: Owner Account
    public $owner_name = '';
    public $owner_email = '';
    public $password = '';
    public $password_confirmation = '';

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

        $owner = null;

        DB::transaction(function () use (&$owner) {
            // 1. Create Vendor (trial, 14 days)
            $vendor = Vendor::create([
                'name' => $this->vendor_name,
                'email' => $this->vendor_email,
                'phone' => $this->vendor_phone,
                'address' => $this->vendor_address,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            // 2. Create default billing config (none — platform admin configures later)
            VendorBillingConfig::create([
                'vendor_id' => $vendor->id,
                'billing_model' => 'none',
            ]);

            // 3. Create Default Settings
            Setting::createDefaultsForVendor($vendor->id);

            // 4. Create Main Branch
            $branch = Branch::create([
                'vendor_id' => $vendor->id,
                'name' => $this->branch_name,
                'address' => $this->branch_address,
                'phone' => $this->branch_phone,
                'email' => $this->branch_email,
                'is_active' => true,
                'is_main' => true,
            ]);

            // 5. Create Owner User
            $owner = User::create([
                'vendor_id' => $vendor->id,
                'name' => $this->owner_name,
                'email' => $this->owner_email,
                'password' => Hash::make($this->password),
                'is_active' => true,
            ]);

            $owner->assignRole('vendor-owner');
            $owner->branches()->attach($branch->id, ['is_primary' => true]);
        });

        // Auto-login
        Auth::login($owner);

        // Set branch session
        $branch = $owner->primaryBranch();
        if ($branch) {
            session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
        }

        session()->regenerate();

        session()->flash('success', 'Welcome! Your account has been created. You have a 14-day free trial.');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.vendor-registration');
    }
}
