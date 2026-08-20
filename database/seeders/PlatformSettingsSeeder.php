<?php

namespace Database\Seeders;

use App\Domains\Platform\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [PlatformSetting::BILLING_ENABLED, true, 'boolean', 'billing', 'Whether platform billing is enforced.'],
            [PlatformSetting::BILLING_GRACE_DAYS, 3, 'integer', 'billing', 'Days after a payment is due before an unpaid account is locked.'],
            [PlatformSetting::BILLING_LOCKDOWN_MODE, 'limited', 'string', 'billing', 'Default lockdown policy once grace expires: limited or total.'],
            [PlatformSetting::BILLING_LOCKDOWN_ALLOWED_ROUTES, json_encode(['dashboard', 'billing.subscription', 'profile.show']), 'json', 'billing', 'Route names still reachable in "limited" lockdown mode.'],
            [PlatformSetting::BILLING_LOCKDOWN_EXPORT_ENABLED, true, 'boolean', 'billing', 'Allow locked-out vendors to export their data.'],
            [PlatformSetting::BILLING_LOCKDOWN_SUPPORT_ENABLED, true, 'boolean', 'billing', 'Allow locked-out vendors to reach a support/contact page.'],
            [PlatformSetting::TRIAL_ENABLED, true, 'boolean', 'billing', 'Whether new plans may offer a free trial.'],
            [PlatformSetting::TRIAL_DEFAULT_DAYS, 14, 'integer', 'billing', 'Default trial length for new pricing plans.'],
            [PlatformSetting::PLATFORM_CURRENCY, 'UGX', 'string', 'general', 'Default platform currency.'],
            [PlatformSetting::PLATFORM_TAX_RATE, 0, 'float', 'general', 'Tax rate applied to platform invoices, if any.'],
        ];

        foreach ($defaults as [$key, $value, $type, $group, $description]) {
            PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'type' => $type,
                    'group' => $group,
                    'description' => $description,
                ]
            );
        }

        PlatformSetting::clearCache();
    }
}
