<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimerViolation extends Model
{
    protected $fillable = [
        'timer_session_id',
        'violation_type',
        'details',
        'detected_at'
    ];

    protected $casts = [
        'detected_at' => 'datetime'
    ];

    public function timerSession()
    {
        return $this->belongsTo(TimerSession::class);
    }
}