<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloridaDeviceSession extends Model
{
    protected $fillable = [
        'user_id',
        'device_type',
        'screen_width',
        'screen_height',
        'user_agent',
        'florida_course_accessed',
        'last_activity',
    ];

    protected $casts = [
        'florida_course_accessed' => 'boolean',
        'last_activity' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
