<?php

namespace App\Domains\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientNarration extends Model
{
    protected $fillable = [
        'job_card_id',
        'issue',
    ];

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }
}
