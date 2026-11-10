<?php

namespace App\Livewire;

use App\Domains\Platform\Services\SelfServiceSubscriptionService;
use Livewire\ComponentHook;

class SubscriptionAccessHook extends ComponentHook
{
    public function hydrate(): void
    {
        $user = auth()->user();
        if (! $user || $user->is_platform_user) {
            return;
        }
        $class = get_class($this->component);
        if ($user->vendor?->vendor_type === 'supplier' && str_starts_with($class, 'App\\Domains\\Marketplace\\Livewire\\Supplier\\')) {
            return; // Selling actions check approval and plan again; existing order fulfilment stays open.
        }
        if (str_starts_with($class, 'App\\Domains\\Platform\\Livewire\\Billing\\')
            || str_starts_with($class, 'App\\Domains\\Notifications\\Livewire\\')
            || $class === \App\Domains\HR\Livewire\Auth\ForcePasswordChange::class) {
            return;
        }
        $service = app(SelfServiceSubscriptionService::class);
        $subscription = $service->current($user->vendor);
        // Abort before property updates, actions or rendering of a stale operational component.
        abort_if($subscription?->self_service && $service->restricted($subscription), 403,
            'Your trial or subscription has ended. Open Billing to continue.');
        abort_if($subscription && ! $subscription->self_service && $subscription->isLocked(), 403,
            'Your subscription is locked. Open Billing to continue.');
    }
}
