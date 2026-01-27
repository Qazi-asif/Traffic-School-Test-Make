<?php

namespace App\Models\Delaware;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizResult extends Model
{
    protected $table = 'delaware_quiz_results';

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
        'rotation_set_used',
        'aggressive_driving_score',
        'insurance_discount_eligible',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'time_spent_minutes' => 'integer',
        'attempt_number' => 'integer',
        'aggressive_driving_score' => 'decimal:2',
        'passed' => 'boolean',
        'insurance_discount_eligible' => 'boolean',
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

    public function scopeByRotationSet($query, $set)
    {
        return $query->where('rotation_set_used', $set);
    }

    public function scopeInsuranceDiscountEligible($query)
    {
        return $query->where('insurance_discount_eligible', true);
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

    public function calculateInsuranceDiscountEligibility()
    {
        // Delaware-specific logic for insurance discount eligibility
        $course = $this->enrollment->course;
        
        if (!$course->insurance_discount_eligible) {
            return false;
        }
        
        // Must pass with at least 80% and complete aggressive driving topics
        return $this->passed && 
               $this->getPercentageAttribute() >= 80 && 
               $this->aggressive_driving_score >= 80;
    }
}