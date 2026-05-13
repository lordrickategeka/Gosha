<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ApiIntegration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'provider',
        'is_active',
        'credentials',
        'webhook_url',
        'webhook_secret',
        'last_tested_at',
        'last_error_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ApiIntegrationLog::class);
    }

    public function setCredentialsAttribute($value): void
    {
        $payload = is_array($value) ? $value : [];
        $encrypted = Crypt::encryptString(json_encode($payload));

        // credentials column is JSON, so encrypted content must be wrapped in valid JSON.
        $this->attributes['credentials'] = json_encode([
            'encrypted_payload' => $encrypted,
        ]);
    }

    public function getCredentialsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        try {
            $decodedValue = json_decode($value, true);

            // Preferred format: JSON object containing encrypted payload.
            if (is_array($decodedValue) && !empty($decodedValue['encrypted_payload'])) {
                $decrypted = Crypt::decryptString((string) $decodedValue['encrypted_payload']);
                $decoded = json_decode($decrypted, true);

                return is_array($decoded) ? $decoded : [];
            }

            // Backward compatibility: raw encrypted string stored directly.
            $decrypted = Crypt::decryptString($value);
            $decoded = json_decode($decrypted, true);

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getCredential(string $key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function providerLabel(): string
    {
        return (string) (config('integrations.providers.' . $this->provider . '.label') ?? ucfirst($this->provider));
    }
}
