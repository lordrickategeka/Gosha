<?php
namespace App\Domains\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryType extends Model
{
    protected $fillable = ['name', 'inventory_category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
