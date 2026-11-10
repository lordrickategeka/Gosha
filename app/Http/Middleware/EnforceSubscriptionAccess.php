<?php

namespace App\Http\Middleware;

use App\Domains\Platform\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscriptionAccess
{
    /**
     * Reachable no matter how locked down the vendor is.
     */
    private const ALWAYS_ALLOWED_ROUTES = [
        'billing.locked',
        'billing.subscription',
        'billing.pay',
        'billing.pay.callback',
        'logout',
        // Forced password change must be resolvable even while locked out,
        // otherwise a user needing both would bounce between this
        // middleware's redirect and EnsurePasswordChanged's redirect.
        'password.change',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('user-guide.*', 'api-docs.*')) {
            return $next($request); // Public documentation contains no account data.
        }
        $user = $request->user();
        abort_if($user && ! $user->is_active, 403, 'Your account has been deactivated.');

        if ($request->routeIs('assistant.*')) {
            return $next($request); // Authenticated documentation help has no operational data access.
        }

        if (! $user || $user->is_platform_user) {
            return $next($request);
        }

        // Livewire's own asset/update endpoints must always pass through,
        // same exemption used by EnsurePasswordChanged.
        if ($request->is('livewire/*')) {
            return $next($request);
        }

        $vendor = $user->vendor;
        if ($vendor?->vendor_type === 'supplier' && ($request->route()?->getName() === 'dashboard' || str_starts_with($request->route()?->getName() ?? '', 'supplier.'))) {
            // Supplier writes have plan guards; dashboard, catalog and existing orders remain available.
            return $next($request);
        }
        $managed = app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class);
        $current = $managed->current($vendor);
        if ($current?->self_service && $managed->restricted($current)) {
            $allowed = array_merge(self::ALWAYS_ALLOWED_ROUTES, ['billing.support', 'billing.export']);
            if (in_array($request->route()?->getName(), $allowed, true)) {
                return $next($request);
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your trial or subscription has ended. Open Billing to continue.', 'billing_url' => route('billing.subscription')], 403);
            }

            return redirect()->route('billing.subscription')->with('error', 'Your trial or subscription has ended. Choose a paid plan to continue.');
        }
        $subscription = $vendor?->activeSubscription;

        if (! $subscription) {
            return $next($request);
        }

        if ($subscription->isInGracePeriod()) {
            // Consumed by components.billing.grace-banner in the app layout.
            view()->share('subscriptionGraceBanner', $subscription);
        }

        if (! $subscription->isLocked()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $alwaysAllowed = self::ALWAYS_ALLOWED_ROUTES;

        if (PlatformSetting::get(PlatformSetting::BILLING_LOCKDOWN_EXPORT_ENABLED, true)) {
            $alwaysAllowed[] = 'billing.export';
        }
        if (PlatformSetting::get(PlatformSetting::BILLING_LOCKDOWN_SUPPORT_ENABLED, true)) {
            $alwaysAllowed[] = 'billing.support';
        }

        if ($routeName && in_array($routeName, $alwaysAllowed, true)) {
            return $next($request);
        }

        if ($vendor->effectiveLockdownMode() === 'limited') {
            $allowedRoutes = (array) PlatformSetting::get(PlatformSetting::BILLING_LOCKDOWN_ALLOWED_ROUTES, []);

            if ($routeName && in_array($routeName, $allowedRoutes, true)) {
                return $next($request);
            }
        }

        return redirect()->route('billing.locked');
    }
}
