<?php

namespace App\Livewire\Platform\Vendors;

use App\Mail\VendorWelcomeMail;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBillingConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
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

    // Step 3: Owner Account
    public $owner_name = '';
    public $owner_email = '';

    // Step 4: Billing Configuration
    public $billing_model = 'none';
    public $subscription_amount = '';
    public $subscription_interval = 'monthly';
    public $transaction_fee_percent = '';
    public $transaction_fee_flat = '';
    public $commission_percent = '';
    public $trial_days = 14;

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
            ]),
            4 => $this->validate([
                'billing_model' => 'required|in:subscription,transaction_fee,commission_cut,hybrid,none',
                'subscription_amount' => 'nullable|numeric|min:0',
                'subscription_interval' => 'nullable|in:monthly,quarterly,yearly',
                'transaction_fee_percent' => 'nullable|numeric|min:0|max:100',
                'transaction_fee_flat' => 'nullable|numeric|min:0',
                'commission_percent' => 'nullable|numeric|min:0|max:100',
                'trial_days' => 'required|integer|min:0|max:365',
            ]),
            default => null,
        };
    }

    public function save()
    {
        // Validate all steps
        $this->currentStep = 1;
        $this->validateCurrentStep();
        $this->currentStep = 2;
        $this->validateCurrentStep();
        $this->currentStep = 3;
        $this->validateCurrentStep();
        $this->currentStep = 4;
        $this->validateCurrentStep();
        $this->currentStep = 5;

        $generatedPassword = Str::random(12);

        DB::transaction(function () use ($generatedPassword) {
            // 1. Create Vendor
            $vendor = Vendor::create([
                'name' => $this->vendor_name,
                'email' => $this->vendor_email,
                'phone' => $this->vendor_phone,
                'address' => $this->vendor_address,
                'status' => 'trial',
                'trial_ends_at' => $this->trial_days > 0 ? now()->addDays($this->trial_days) : null,
            ]);

            // 2. Create Billing Config
            VendorBillingConfig::create([
                'vendor_id' => $vendor->id,
                'billing_model' => $this->billing_model,
                'subscription_amount' => $this->subscription_amount ?: null,
                'subscription_interval' => $this->subscription_interval,
                'transaction_fee_percent' => $this->transaction_fee_percent ?: null,
                'transaction_fee_flat' => $this->transaction_fee_flat ?: null,
                'commission_percent' => $this->commission_percent ?: null,
                'next_billing_date' => $this->billing_model !== 'none' ? now()->addDays($this->trial_days > 0 ? $this->trial_days : 30) : null,
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
                'password' => Hash::make($generatedPassword),
                'is_active' => true,
                'must_change_password' => true,
            ]);

            $owner->assignRole('vendor-owner');
            $owner->branches()->attach($branch->id, ['is_primary' => true]);

            // 6. Send Welcome Email
            Mail::to($owner->email)->send(new VendorWelcomeMail($vendor, $owner, $generatedPassword));
        });

        session()->flash('success', "Vendor '{$this->vendor_name}' created successfully. Temporary password: {$generatedPassword}");

        return $this->redirect(route('platform.vendors.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.platform.vendors.create');
    }
}
