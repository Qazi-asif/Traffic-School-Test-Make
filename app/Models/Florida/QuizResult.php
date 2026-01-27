<?php

namespace App\Models\Florida;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizResult extends Model
{
    protected $table = 'florida_quiz_results';

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'quiz_id',
        'chapter_id',
        'attempt_number',
        'score',
        'total_questions',
        'correct_answers',
        'time_spent_minutes',
        'passed',
        'answers_json',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'time_spent_minutes' => 'integer',
        'attempt_number' => 'integer',
        'passed' => 'boolean',
        'answers_json' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(ChapterQuiz::class, 'quiz_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('passed', false);
    }

    public function scopeLatestAttempt($query)
    {
        return $query->orderBy('attempt_number', 'desc');
    }

    public function getPercentageAttribute()
    {
        if ($this->total_questions == 0) {
            return 0;
        }
        
        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }

    public function getGradeAttribute()
    {
        $percentage = $this->getPercentageAttribute();
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        
        return 'F';
    }
}