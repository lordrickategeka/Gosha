<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryType extends Model
{
    protected $fillable = ['name', 'inventory_category_id'];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
