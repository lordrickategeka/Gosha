<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TestLogin extends Command
{
    protected $signature = 'test:login {email} {password}';

    protected $description = 'Run a test login attempt and emit logs for debugging';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        Log::info('TestLogin: starting login attempt', ['email' => $email]);

        try {
            if (!Auth::attempt(['email' => $email, 'password' => $password])) {
                Log::warning('TestLogin: invalid credentials', ['email' => $email]);
                $this->error('Invalid credentials');
                return 1;
            }

            /** @var User $user */
            $user = Auth::user();
            Log::info('TestLogin: authenticated', ['user_id' => $user->id, 'email' => $user->email]);

            if (!$user->is_active) {
                Auth::logout();
                Log::warning('TestLogin: user deactivated', ['user_id' => $user->id]);
                $this->error('User deactivated');
                return 1;
            }

            if (!$user->is_platform_user && $user->vendor) {
                if ($user->vendor->status === 'suspended') {
                    Auth::logout();
                    Log::warning('TestLogin: vendor suspended', ['vendor_id' => $user->vendor->id]);
                    $this->error('Vendor suspended');
                    return 1;
                }

                if (method_exists($user->vendor, 'isTrialExpired') && $user->vendor->isTrialExpired()) {
                    Auth::logout();
                    Log::warning('TestLogin: vendor trial expired', ['vendor_id' => $user->vendor->id]);
                    $this->error('Vendor trial expired');
                    return 1;
                }
            }

            Log::info('TestLogin: success', ['user_id' => $user->id]);
            $this->info('Login successful for user id ' . $user->id);
            return 0;
        } catch (\Exception $e) {
            Log::error('TestLogin: exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->error('Exception: ' . $e->getMessage());
            return 1;
        }
    }
}
