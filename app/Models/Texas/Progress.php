<?php

namespace App\Models\Texas;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $table = 'texas_progress';

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'chapter_id',
        'started_at',
        'completed_at',
        'time_spent_minutes',
        'progress_percentage',
        'is_completed',
        'last_position',
        'quiz_passed',
        'quiz_attempts',
        'quiz_best_score',
        'video_completed',
        'video_watch_time',
        'proctoring_session_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent_minutes' => 'integer',
        'progress_percentage' => 'integer',
        'is_completed' => 'boolean',
        'quiz_passed' => 'boolean',
        'quiz_attempts' => 'integer',
        'quiz_best_score' => 'decimal:2',
        'video_completed' => 'boolean',
        'video_watch_time' => 'integer',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeVideoCompleted($query)
    {
        return $query->where('video_completed', true);
    }

    public function markAsStarted()
    {
        if (!$this->started_at) {
            $this->update(['started_at' => now()]);
        }
    }

    public function markAsCompleted()
    {
        $this->update([
            'completed_at' => now(),
            'is_completed' => true,
            'progress_percentage' => 100,
        ]);
    }

    public function markVideoCompleted($watchTime = null)
    {
        $data = ['video_completed' => true];
        
        if ($watchTime !== null) {
            $data['video_watch_time'] = $watchTime;
        }
        
        $this->update($data);
    }

    public function updateProgress($percentage, $position = null)
    {
        $data = [
            'progress_percentage' => min(100, max(0, $percentage)),
        ];
        
        if ($position !== null) {
            $data['last_position'] = $position;
        }
        
        // Texas-specific: Check video completion requirement
        $chapter = $this->chapter;
        if ($chapter->requires_video_completion && !$this->video_completed) {
            $data['progress_percentage'] = min(50, $data['progress_percentage']);
        }
        
        if ($percentage >= 100 && (!$chapter->requires_video_completion || $this->video_completed)) {
            $data['completed_at'] = now();
            $data['is_completed'] = true;
        }
        
        $this->update($data);
    }

    public function canComplete()
    {
        $chapter = $this->chapter;
        
        if ($chapter->requires_video_completion && !$this->video_completed) {
            return false;
        }
        
        return $this->progress_percentage >= 100;
    }
}