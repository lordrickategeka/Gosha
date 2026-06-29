<?php

namespace App\Domains\Operations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WorkOrderItemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_item_id',
        'path',
        'caption',
    ];

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
