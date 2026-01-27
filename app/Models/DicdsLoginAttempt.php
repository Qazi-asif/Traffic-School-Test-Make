<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DicdsLoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'attempted_at',
        'successful',
        'lockout_triggered',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'successful' => 'boolean',
        'lockout_triggered' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
