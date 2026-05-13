<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiIntegrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_integration_id',
        'action',
        'status',
        'details',
        'error_message',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(ApiIntegration::class, 'api_integration_id');
    }
}
