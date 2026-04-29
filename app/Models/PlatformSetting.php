<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    // Setting keys
    const PLATFORM_NAME = 'platform_name';
    const PLATFORM_EMAIL = 'platform_email';
    const PLATFORM_PHONE = 'platform_phone';
    const PLATFORM_ADDRESS = 'platform_address';
    const PLATFORM_CURRENCY = 'platform_currency';
    const PLATFORM_TAX_RATE = 'platform_tax_rate';
    const PLATFORM_TAX_NAME = 'platform_tax_name';

    const BILLING_ENABLED = 'billing_enabled';
    const BILLING_GRACE_DAYS = 'billing_grace_days';
    const BILLING_AUTO_SUSPEND = 'billing_auto_suspend';
    const BILLING_SUSPEND_AFTER_DAYS = 'billing_suspend_after_days';

    const TRIAL_ENABLED = 'trial_enabled';
    const TRIAL_DEFAULT_DAYS = 'trial_default_days';
    const TRIAL_EXTEND_ALLOWED = 'trial_extend_allowed';

    const COMMISSION_PAYOUT_DAY = 'commission_payout_day';
    const COMMISSION_MIN_PAYOUT = 'commission_min_payout';

    const SMS_ENABLED = 'sms_enabled';
    const SMS_PROVIDER = 'sms_provider';
    const SMS_API_KEY = 'sms_api_key';

    const PAYMENT_METHODS = 'payment_methods';
    const MOBILE_MONEY_ENABLED = 'mobile_money_enabled';
    const BANK_TRANSFER_ENABLED = 'bank_transfer_enabled';
    const CARD_PAYMENTS_ENABLED = 'card_payments_enabled';

    public static function get(string $key, $default = null)
    {
        return Cache::remember("platform_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("platform_setting_{$key}");
    }

    public static function getGroup(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            })
            ->toArray();
    }

    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float', 'decimal' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    public static function clearCache(): void
    {
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("platform_setting_{$key}");
        }
    }
}
