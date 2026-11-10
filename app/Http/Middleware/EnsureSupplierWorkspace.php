<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupplierWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $user->is_platform_user) {
            abort_unless($user->is_active, 403);
            $vendor = $user->vendor;
            $request->session()->put('current_vendor_id', $user->vendor_id);
            $hasModule = in_array($vendor?->vendor_type, ['supplier', 'hybrid'], true);
            $hasRole = $user->hasAnyRole(['supplier_admin', 'supplier_staff']);
            if (! $hasModule || ! $hasRole) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Supplier access is not enabled for your account. Ask the business owner to open Supplier module.', 'supplier_module_url' => route('supplier.enable')], 403);
                }

                return redirect()->route('supplier.enable');
            }
        }

        return $next($request);
    }
}
