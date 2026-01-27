<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = [
        'enrollment_id',
        'chapter_id',
        'questions_attempted',
        'score',
        'total_questions',
        'passed',
        'time_spent',
        'attempted_at',
        'completed_at',
    ];

    protected $casts = [
        'questions_attempted' => 'array',
        'passed' => 'boolean',
        'attempted_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
