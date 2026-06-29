<?php

namespace App\Domains\ServiceConfig\Models;

use App\Shared\Traits\BelongsToVendor;
use App\Shared\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use HasFactory, SoftDeletes, BelongsToVendor, HasAuditLog;

    protected $fillable = [
        'vendor_id',
        'name',
        'document_type',
        'is_default',
        'is_active',
        'template_schema',
        'page_size',
        'orientation',
        'margins',
        'primary_color',
        'secondary_color',
        'font_family',
        'font_size',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'template_schema' => 'array',
        'margins' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Ensure only one default template per type per vendor
        static::saving(function ($template) {
            if ($template->is_default) {
                static::where('vendor_id', $template->vendor_id)
                    ->where('document_type', $template->document_type)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(TemplateUsageLog::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDocumentType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Helpers
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function getDefaultSchemaAttribute(): array
    {
        // Return a basic schema if template_schema is empty
        if (empty($this->template_schema)) {
            return [
                'version' => '1.0',
                'sections' => [],
            ];
        }

        return $this->template_schema;
    }

    public function logUsage($documentType, $documentId): void
    {
        $this->usageLogs()->create([
            'document_type' => $documentType,
            'document_id' => $documentId,
            'generated_by' => auth()->id(),
        ]);
    }
}
