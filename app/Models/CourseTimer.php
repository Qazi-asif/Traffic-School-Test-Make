<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTimer extends Model
{
    protected $table = 'chapter_timers';
    
    protected $fillable = [
        'chapter_id',
        'chapter_type',
        'required_time_minutes',
        'is_enabled',
        'allow_pause',
        'bypass_for_admin',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allow_pause' => 'boolean',
        'bypass_for_admin' => 'boolean',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function sessions()
    {
        return $this->hasMany(TimerSession::class, 'course_timer_id');
    }
}
