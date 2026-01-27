<?php

namespace App\Models\Florida;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $table = 'florida_progress';

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

    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false)
            ->whereNotNull('started_at');
    }

    public function scopeNotStarted($query)
    {
        return $query->whereNull('started_at');
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

    public function updateProgress($percentage, $position = null)
    {
        $data = [
            'progress_percentage' => min(100, max(0, $percentage)),
        ];
        
        if ($position !== null) {
            $data['last_position'] = $position;
        }
        
        if ($percentage >= 100) {
            $data['completed_at'] = now();
            $data['is_completed'] = true;
        }
        
        $this->update($data);
    }

    public function addTimeSpent($minutes)
    {
        $this->increment('time_spent_minutes', $minutes);
    }
}