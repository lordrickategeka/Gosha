<?php

namespace App\Models;

use App\Traits\BelongsToVendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheckTemplate extends Model
{
    use HasFactory, BelongsToVendor;

    protected $fillable = [
        'vendor_id',
        'section',
        'item_name',
        'sort_order',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Sections available in the checklist
     */
    public const SECTIONS = [
        'exterior' => 'A. Exterior',
        'interior' => 'B. Interior',
        'engine_compartment' => 'C. Engine Compartment',
        'underbody_suspension' => 'D. Underbody & Suspension',
        'road_test' => 'E. Road Test (Optional)',
    ];

    /**
     * Get vendor relationship
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Scope: Get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get default system templates
     */
    public function scopeDefaults($query)
    {
        return $query->whereNull('vendor_id')->where('is_default', true);
    }

    /**
     * Scope: Get templates by section
     */
    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section);
    }

    /**
     * Get templates for a specific vendor or defaults
     */
    public static function getForVendor(?int $vendorId = null, ?string $section = null)
    {
        $query = static::query()
            ->where(function ($q) use ($vendorId) {
                $q->whereNull('vendor_id') // System defaults
                  ->orWhere('vendor_id', $vendorId); // Vendor specific
            })
            ->active()
            ->orderBy('section')
            ->orderBy('sort_order');

        if ($section) {
            $query->where('section', $section);
        }

        return $query->get()
            ->groupBy('section');
    }
}
