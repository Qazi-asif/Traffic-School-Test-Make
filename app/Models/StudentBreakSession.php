<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentBreakSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enrollment_id',
        'chapter_break_id',
        'break_started_at',
        'break_ends_at',
        'break_completed_at',
        'is_completed',
        'was_skipped',
        'break_data'
    ];

    protected $casts = [
        'break_started_at' => 'datetime',
        'break_ends_at' => 'datetime',
        'break_completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'was_skipped' => 'boolean',
        'break_data' => 'array',
    ];

    /**
     * Get the user this break session belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the enrollment this break session belongs to
     */
    public function enrollment()
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    /**
     * Get the chapter break this session is for
     */
    public function chapterBreak()
    {
        return $this->belongsTo(ChapterBreak::class);
    }

    /**
     * Check if break is currently active
     */
    public function getIsActiveAttribute()
    {
        return !$this->is_completed && !$this->was_skipped && Carbon::now()->lt($this->break_ends_at);
    }

    /**
     * Check if break has expired
     */
    public function getIsExpiredAttribute()
    {
        return !$this->is_completed && Carbon::now()->gte($this->break_ends_at);
    }

    /**
     * Get remaining time in minutes
     */
    public function getRemainingMinutesAttribute()
    {
        if ($this->is_completed || $this->was_skipped) {
            return 0;
        }
        
        $remaining = Carbon::now()->diffInMinutes($this->break_ends_at, false);
        return max(0, $remaining);
    }

    /**
     * Get formatted remaining time
     */
    public function getFormattedRemainingTimeAttribute()
    {
        $minutes = $this->remaining_minutes;
        
        if ($minutes <= 0) {
            return 'Break completed';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$mins}m remaining";
        } else {
            return "{$mins}m remaining";
        }
    }

    /**
     * Mark break as completed
     */
    public function markCompleted()
    {
        $this->update([
            'is_completed' => true,
            'break_completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark break as skipped
     */
    public function markSkipped()
    {
        $this->update([
            'was_skipped' => true,
            'break_completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Scope for active break sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_completed', false)
                    ->where('was_skipped', false)
                    ->where('break_ends_at', '>', Carbon::now());
    }

    /**
     * Scope for completed break sessions
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
}