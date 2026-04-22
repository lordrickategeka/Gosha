<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            // Allow access to the password change page, logout, and livewire routes
            $allowed = ['password.change', 'logout'];
            if (!$request->routeIs(...$allowed) && !$request->is('livewire/*')) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
