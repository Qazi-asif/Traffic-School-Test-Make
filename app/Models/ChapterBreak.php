<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'course_type',
        'after_chapter_id',
        'break_title',
        'break_message',
        'break_duration_hours',
        'break_duration_minutes',
        'is_mandatory',
        'is_active',
        'break_settings'
    ];

    protected $casts = [
        'break_settings' => 'array',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the total break duration in minutes
     */
    public function getTotalDurationMinutesAttribute()
    {
        return ($this->break_duration_hours * 60) + $this->break_duration_minutes;
    }

    /**
     * Get formatted duration string
     */
    public function getFormattedDurationAttribute()
    {
        $hours = $this->break_duration_hours;
        $minutes = $this->break_duration_minutes;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Get the course this break belongs to
     */
    public function course()
    {
        if ($this->course_type === 'florida_courses') {
            return $this->belongsTo(FloridaCourse::class, 'course_id');
        }
        
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the chapter after which this break occurs
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'after_chapter_id');
    }

    /**
     * Get student break sessions for this break
     */
    public function breakSessions()
    {
        return $this->hasMany(StudentBreakSession::class);
    }

    /**
     * Scope for active breaks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific course type
     */
    public function scopeForCourseType($query, $courseType)
    {
        return $query->where('course_type', $courseType);
    }
}