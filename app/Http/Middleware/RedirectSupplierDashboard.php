<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectSupplierDashboard
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && ! $user->is_platform_user && $user->vendor?->vendor_type === 'supplier') {
            return redirect()->route('supplier.dashboard');
        }

        return $next($request);
    }
}
