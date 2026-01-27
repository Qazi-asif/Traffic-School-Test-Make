<?php

namespace App\Models\Missouri;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $table = 'missouri_progress';

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
        'quiz_set_used',
        'form_4444_eligible',
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
        'form_4444_eligible' => 'boolean',
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

    public function scopeForm4444Eligible($query)
    {
        return $query->where('form_4444_eligible', true);
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
        
        // Check if eligible for Form 4444
        $this->checkForm4444Eligibility();
    }

    public function checkForm4444Eligibility()
    {
        // Missouri-specific: Check if all required chapters are completed
        $enrollment = $this->enrollment;
        $totalChapters = $enrollment->course->chapters()->count();
        $completedChapters = $enrollment->progress()->completed()->count();
        
        if ($completedChapters >= $totalChapters) {
            $this->update(['form_4444_eligible' => true]);
        }
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
        
        if ($percentage >= 100) {
            $this->checkForm4444Eligibility();
        }
    }
}