<?php

namespace App\Domains\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SubscriptionNotice extends Model
{
    protected $guarded = [];

    protected $casts = ['available_at' => 'datetime', 'sent_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
