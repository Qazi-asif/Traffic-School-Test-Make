<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimerSession extends Model
{
    protected $fillable = [
        'user_id',
        'course_timer_id',
        'chapter_id',
        'started_at',
        'ended_at',
        'completed_at',
        'time_spent_seconds',
        'duration_minutes',
        'session_token',
        'is_active',
        'is_completed',
        'bypassed_by_admin',
        'browser_fingerprint',
        'ip_address',
        'tab_switches',
        'page_reloads',
        'focus_losses',
        'resume_count',
        'resumed_at',
        'last_heartbeat',
        'bypassed_by_user_id'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'resumed_at' => 'datetime',
        'last_heartbeat' => 'datetime',
        'is_completed' => 'boolean',
        'bypassed_by_admin' => 'boolean',
        'is_active' => 'boolean',
        'time_spent_seconds' => 'integer',
        'tab_switches' => 'integer',
        'page_reloads' => 'integer',
        'focus_losses' => 'integer',
        'resume_count' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timer()
    {
        return $this->belongsTo(CourseTimer::class, 'course_timer_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function violations()
    {
        return $this->hasMany(TimerViolation::class);
    }

    public function bypassedByUser()
    {
        return $this->belongsTo(User::class, 'bypassed_by_user_id');
    }
}
