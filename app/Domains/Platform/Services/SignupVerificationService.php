<?php

namespace App\Domains\Platform\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SignupVerificationService
{
    private function key(string $token, string $channel): string
    {
        return 'signup-proof:'.hash('sha256', session()->getId().':'.$token.':'.$channel);
    }

    public function verified(string $token, string $identifier, string $channel = 'email'): bool
    {
        $proof = Cache::get($this->key($token, $channel));

        return $identifier !== '' && ($proof['identifier'] ?? null) === $identifier && ($proof['verified'] ?? false);
    }

    public function request(string $token, string $identifier, string $channel = 'email'): void
    {
        $key = 'signup-send:'.hash('sha256', $identifier);
        $ipKey = 'signup-send-ip:'.hash('sha256', request()->ip());
        if (RateLimiter::tooManyAttempts($key, 1) || RateLimiter::tooManyAttempts($ipKey, 20)) {
            throw ValidationException::withMessages(['verification' => 'Please wait before requesting another code.']);
        }
        RateLimiter::hit($key, 60);
        RateLimiter::hit($ipKey, 900);
        $result = app(OnePassConnector::class)->otp('request', $identifier);
        if (! $result['success']) {
            if (($result['status'] ?? 0) === 429) {
                RateLimiter::clear($key);
                RateLimiter::hit($key, $result['retry_after']);
            }
            throw ValidationException::withMessages(['verification' => $result['message']]);
        }
        Cache::put($this->key($token, $channel), ['identifier' => $identifier, 'verified' => false], now()->addMinutes(5));
    }

    public function verify(string $token, string $identifier, string $code, string $channel = 'email'): void
    {
        $key = 'signup-check:'.hash('sha256', request()->ip().':'.$identifier);
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages(['verification' => 'Too many attempts. Please try again later.']);
        }
        RateLimiter::hit($key, 900);
        $proof = Cache::get($this->key($token, $channel));
        if (($proof['identifier'] ?? null) !== $identifier) {
            throw ValidationException::withMessages(['verification' => 'Request a new code for this address or number.']);
        }
        $result = app(OnePassConnector::class)->otp('verify', $identifier, $code);
        if (! $result['success']) {
            if (($result['status'] ?? 0) === 429) {
                RateLimiter::clear($key);
                for ($attempt = 0; $attempt < 10; $attempt++) {
                    RateLimiter::hit($key, $result['retry_after']);
                }
            }
            throw ValidationException::withMessages(['verification' => $result['message']]);
        }
        Cache::put($this->key($token, $channel), ['identifier' => $identifier, 'verified' => true], now()->addMinutes(30));
    }

    public function forget(string $token): void
    {
        Cache::forget($this->key($token, 'email'));
        Cache::forget($this->key($token, 'phone'));
    }
}
