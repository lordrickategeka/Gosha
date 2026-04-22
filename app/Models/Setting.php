<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'group',
        'key',
        'value',
        'type',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // Scopes
    public function scopeForVendor($query, ?int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopePlatform($query)
    {
        return $query->whereNull('vendor_id');
    }

    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    // Accessors
    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    // Static helpers
    public static function get(string $key, $default = null, ?int $vendorId = null)
    {
        $cacheKey = "settings.{$vendorId}.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $vendorId) {
            $setting = static::where('key', $key)
                ->where('vendor_id', $vendorId)
                ->first();

            return $setting ? $setting->typed_value : $default;
        });
    }

    public static function set(string $key, $value, string $group = 'general', ?int $vendorId = null, string $type = 'string'): self
    {
        // Convert value based on type
        $storedValue = match ($type) {
            'json', 'array' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            [
                'vendor_id' => $vendorId,
                'key' => $key,
            ],
            [
                'group' => $group,
                'value' => $storedValue,
                'type' => $type,
            ]
        );

        // Clear cache
        Cache::forget("settings.{$vendorId}.{$key}");

        return $setting;
    }

    public static function getGroup(string $group, ?int $vendorId = null): array
    {
        return static::where('group', $group)
            ->where('vendor_id', $vendorId)
            ->get()
            ->pluck('typed_value', 'key')
            ->toArray();
    }

    public static function setMany(array $settings, string $group = 'general', ?int $vendorId = null): void
    {
        foreach ($settings as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_float($value) => 'float',
                is_array($value) => 'array',
                default => 'string',
            };

            static::set($key, $value, $group, $vendorId, $type);
        }
    }

    // Default settings for new vendors
    public static function createDefaultsForVendor(int $vendorId): void
    {
        $defaults = [
            'general' => [
                'currency' => 'UGX',
                'timezone' => 'Africa/Kampala',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
            ],
            'invoice' => [
                'tax_rate' => 18,
                'due_days' => 14,
                'prefix' => 'INV',
                'footer_text' => 'Thank you for your business!',
                'show_bank_details' => true,
            ],
            'notification' => [
                'email_on_new_order' => true,
                'email_on_payment' => true,
                'sms_reminders' => false,
            ],
            'loyalty' => [
                'enabled' => true,
                'points_per_currency' => 0.01, // 1 point per 100 UGX
                'redemption_rate' => 100, // 100 points = 1 UGX
            ],
        ];

        foreach ($defaults as $group => $settings) {
            foreach ($settings as $key => $value) {
                $type = match (true) {
                    is_bool($value) => 'boolean',
                    is_int($value) => 'integer',
                    is_float($value) => 'float',
                    is_array($value) => 'array',
                    default => 'string',
                };

                static::set($key, $value, $group, $vendorId, $type);
            }
        }
    }
}
