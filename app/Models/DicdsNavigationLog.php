<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DicdsNavigationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'navigation_action',
        'page_url',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
