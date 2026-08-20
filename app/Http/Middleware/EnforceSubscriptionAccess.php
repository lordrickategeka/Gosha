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
        $user = $request->user();

        if (!$user || $user->is_platform_user) {
            return $next($request);
        }

        // Livewire's own asset/update endpoints must always pass through,
        // same exemption used by EnsurePasswordChanged.
        if ($request->is('livewire/*')) {
            return $next($request);
        }

        $vendor = $user->vendor;
        $subscription = $vendor?->activeSubscription;

        if (!$subscription) {
            return $next($request);
        }

        if ($subscription->isInGracePeriod()) {
            // Consumed by components.billing.grace-banner in the app layout.
            view()->share('subscriptionGraceBanner', $subscription);
        }

        if (!$subscription->isLocked()) {
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
