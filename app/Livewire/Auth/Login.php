<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Rule('required|email')]
    public $email = '';

    #[Rule('required')]
    public $password = '';

    public $remember = false;

    public function login()
    {
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'These credentials do not match our records.');
            return;
        }

        /** @var User $user */
        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            $this->addError('email', 'Your account has been deactivated. Please contact support.');
            return;
        }

        // Check if vendor is active (for non-platform users)
        if (!$user->is_platform_user && $user->vendor) {
            if ($user->vendor->status === 'suspended') {
                Auth::logout();
                $this->addError('email', 'Your organization account has been suspended. Please contact support.');
                return;
            }

            if ($user->vendor->isTrialExpired()) {
                Auth::logout();
                $this->addError('email', 'Your trial period has expired. Please upgrade your subscription.');
                return;
            }
        }

        // Set default branch in session for both vendor and platform users.
        $primaryBranch = $user->primaryBranch();
        if ($primaryBranch) {
            session(['current_branch_id' => $primaryBranch->id, 'current_branch_name' => $primaryBranch->name]);
        } elseif ($user->branches->count() > 0) {
            $branch = $user->branches->first();
            session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
        }

        // Log the login
        AuditLog::logLogin($user);

        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
