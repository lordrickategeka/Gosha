<?php

namespace App\Support;

use App\Models\User;

class AssistantKnowledge
{
    public function __construct(private UserGuide $guide) {}

    public function role(User $user): string
    {
        if ($user->is_platform_user) {
            return 'platform_admin';
        }
        if ($user->vendor?->vendor_type === 'supplier') {
            return $user->hasRole('supplier_admin') ? 'supplier_admin' : 'supplier_staff';
        }

        return $user->hasRole('vendor-owner') ? 'garage_owner' : 'garage_staff';
    }

    public function guides(?User $user = null): array
    {
        $audiences = ! $user || $user->is_platform_user ? array_keys(UserGuide::AUDIENCES)
            : ($user->vendor?->vendor_type === 'supplier' ? ['all', 'supplier']
                : ($user->vendor?->vendor_type === 'hybrid' && $user->hasAnyRole(['supplier_admin', 'supplier_staff']) ? ['all', 'garage', 'supplier', 'staff'] : ['all', 'garage', 'staff']));

        return array_values(array_map(fn ($chapter) => [
            'id' => $chapter['id'], 'title' => $chapter['title'],
            'content' => $chapter['body'], 'source' => 'GarageHQ user guide',
            'primaryPath' => '/user-guide#'.$chapter['id'],
        ], array_filter($this->guide->chapters(), fn ($chapter) => array_intersect($audiences, $chapter['audiences']))));
    }

    public function documents(User $user): array
    {
        return ['overview' => ['intro' => 'GarageHQ is a garage, car-wash and parts-supplier management system. Explain the documented workflow for the user role. These guides describe features, not the user\'s live account or records. Trial dates, prices, limits and permissions must be confirmed in the app. If help is missing, refer to the business administrator.',
            'concepts' => [['title' => 'Account creation and access', 'body' => 'Only garage and supplier business owners use the two registration cards. Staff obtain an account from their business administrator and use the standard email/password login form. There is no staff or platform-administrator self-registration card. Platform administrators use their existing authorised account; do not tell them to register a business to access administration. Supplier trials start after business approval; paid plans require verified payment.'],
                ['title' => 'Supplier module for an existing garage', 'body' => 'Garage owners use Marketplace → Supplier module or Modules → Add supplier module with their existing account. They submit a business application; platform approval is required before selling. The same garage subscription and expiry remain in effect. No second account, new subscription, new trial or automatic payment is created. Normal marketplace commission applies. After approval, Supplier team grants/removes supplier staff access without removing existing garage roles or logins.']]],
            'supportGuides' => $this->guides($user), 'moduleGuides' => []];
    }
}
