<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Registration;

use App\Domains\Platform\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Public self-service supplier signup. Deliberately separate from VendorRegistration
 * (garage onboarding) rather than a branch of it — suppliers get no branch and no
 * subscription (both confirmed safe to omit: EnsureBranchSession/EnforceSubscriptionAccess
 * degrade gracefully with neither present). The account is created and logged in
 * immediately with `is_verified_supplier = false`; the pending-verification banner on the
 * supplier dashboard is what actually gates catalog/listing management, not this flow.
 *
 * A short 3-step wizard (business -> login -> review), mirroring the step-wizard pattern
 * used by VendorRegistration and the admin Vendors\Create component, rather than one long
 * single-page form.
 */
#[Layout('components.layouts.guest')]
class Register extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 3;

    public string $business_name = '';
    public string $business_email = '';
    public string $business_phone = '';
    public string $business_address = '';

    public string $owner_name = '';
    public string $owner_email = '';
    public string $password = '';
    public string $password_confirmation = '';

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
                'business_name' => 'required|string|max:255',
                'business_email' => 'required|email|unique:vendors,email',
                'business_phone' => 'nullable|string|max:50',
                'business_address' => 'nullable|string|max:500',
            ]),
            2 => $this->validate([
                'owner_name' => 'required|string|max:255',
                'owner_email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]),
            default => null,
        };
    }

    public function register()
    {
        // Re-validate every step — a step could otherwise be skipped via goToStep/back-nav.
        $this->currentStep = 1;
        $this->validateCurrentStep();
        $this->currentStep = 2;
        $this->validateCurrentStep();
        $this->currentStep = 3;

        $owner = null;

        DB::transaction(function () use (&$owner) {
            $vendor = Vendor::create([
                'name' => $this->business_name,
                'email' => $this->business_email,
                'phone' => $this->business_phone ?: null,
                'address' => $this->business_address ?: null,
                'status' => 'active',
                'vendor_type' => 'supplier',
                'is_verified_supplier' => false,
            ]);

            $owner = User::create([
                'vendor_id' => $vendor->id,
                'name' => $this->owner_name,
                'email' => $this->owner_email,
                'password' => Hash::make($this->password),
                'is_active' => true,
            ]);

            $owner->assignRole('supplier_admin');
        });

        Auth::login($owner);
        session()->regenerate();

        session()->flash('success', 'Welcome! Your supplier account has been created and is pending verification.');

        return $this->redirectRoute('supplier.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.marketplace.supplier.registration.register');
    }
}
