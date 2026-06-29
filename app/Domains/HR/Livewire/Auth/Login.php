<?php

namespace App\Domains\HR\Livewire\Auth;

use App\Domains\Organization\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        if (!$user->is_active) {
            Auth::logout();
            $this->addError('email', 'Your account has been deactivated. Please contact support.');
            return;
        }

        if (!$user->is_platform_user && $user->vendor) {
            if ($user->vendor->status === 'suspended') {
                Auth::logout();
                $this->addError('email', 'Your organization account has been suspended. Please contact support.');
                return;
            }

            if (method_exists($user->vendor, 'isTrialExpired') && $user->vendor->isTrialExpired()) {
                Auth::logout();
                $this->addError('email', 'Your trial period has expired. Please upgrade your subscription.');
                return;
            }
        }

        $primaryBranch = $user->primaryBranch();
        if ($primaryBranch) {
            session(['current_branch_id' => $primaryBranch->id, 'current_branch_name' => $primaryBranch->name]);
        } elseif ($user->branches->count() > 0) {
            $branch = $user->branches->first();
            session(['current_branch_id' => $branch->id, 'current_branch_name' => $branch->name]);
        }

        try {
            AuditLog::logLogin($user);
        } catch (\Throwable $e) {
            Log::warning('AuditLog failed on login', ['error' => $e->getMessage()]);
        }

        $this->redirectIntended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
