<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    public static function getDefault(): self
    {
        return static::where('code', 'UGX')->first();
    }

    public function formatAmount(float $amount): string
    {
        return $this->symbol . ' ' . number_format($amount, 2);
    }
}
