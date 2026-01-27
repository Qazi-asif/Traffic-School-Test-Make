<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaNotification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'action_url', 'is_read', 'read_at',
        'scheduled_for', 'sent_at', 'florida_metadata',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'florida_metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
