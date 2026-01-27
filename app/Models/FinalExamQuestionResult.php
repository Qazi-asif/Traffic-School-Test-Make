<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalExamQuestionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'final_exam_result_id',
        'question_id',
        'student_answer',
        'correct_answer',
        'is_correct',
        'points_earned',
        'points_possible',
        'time_spent_seconds'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the final exam result this belongs to
     */
    public function finalExamResult()
    {
        return $this->belongsTo(FinalExamResult::class);
    }

    /**
     * Get the question details
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get formatted time spent
     */
    public function getFormattedTimeSpentAttribute()
    {
        $seconds = $this->time_spent_seconds;
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        } else {
            return "{$secs}s";
        }
    }

    /**
     * Get answer status color
     */
    public function getAnswerStatusColorAttribute()
    {
        return $this->is_correct ? 'success' : 'danger';
    }
}