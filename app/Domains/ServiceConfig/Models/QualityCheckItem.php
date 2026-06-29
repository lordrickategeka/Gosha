<?php

namespace App\Domains\ServiceConfig\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quality_check_id',
        'section',
        'item_name',
        'status',
        'remarks',
    ];

    /**
     * Get the quality check
     */
    public function qualityCheck(): BelongsTo
    {
        return $this->belongsTo(QualityCheck::class);
    }

    /**
     * Scope: Items needing attention
     */
    public function scopeNeedsAttention($query)
    {
        return $query->where('status', 'needs_attention');
    }

    /**
     * Scope: OK items
     */
    public function scopeOk($query)
    {
        return $query->where('status', 'ok');
    }

    /**
     * Check if item needs attention
     */
    public function needsAttention(): bool
    {
        return $this->status === 'needs_attention';
    }

    /**
     * Check if item is OK
     */
    public function isOk(): bool
    {
        return $this->status === 'ok';
    }
}
