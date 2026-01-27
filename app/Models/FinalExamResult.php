<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinalExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enrollment_id',
        'course_id',
        'course_type',
        'score',
        'passed',
        'final_exam_correct',
        'final_exam_total',
        'exam_completed_at',
        'exam_duration_minutes',
        'quiz_average',
        'free_response_score',
        'overall_score',
        'grade_letter',
        'status',
        'passing_threshold',
        'student_feedback',
        'student_rating',
        'student_feedback_at',
        'grading_period_ends_at',
        'grading_completed',
        'instructor_notes',
        'graded_by',
        'graded_at',
        'certificate_generated',
        'certificate_number',
        'certificate_issued_at'
    ];

    protected $casts = [
        'exam_completed_at' => 'datetime',
        'student_feedback_at' => 'datetime',
        'grading_period_ends_at' => 'datetime',
        'graded_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
        'passed' => 'boolean',
        'grading_completed' => 'boolean',
        'certificate_generated' => 'boolean',
    ];

    /**
     * Get the user who took the exam
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the enrollment this result belongs to
     */
    public function enrollment()
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    /**
     * Get the course this result is for
     */
    public function course()
    {
        if ($this->course_type === 'florida_courses') {
            return $this->belongsTo(FloridaCourse::class, 'course_id');
        }
        
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the instructor who graded this result
     */
    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get detailed question results
     */
    public function questionResults()
    {
        return $this->hasMany(FinalExamQuestionResult::class);
    }

    /**
     * Calculate overall score based on components
     */
    public function calculateOverallScore()
    {
        // Default weights (can be customized per course)
        $quizWeight = 0.3; // 30%
        $freeResponseWeight = 0.2; // 20%
        $finalExamWeight = 0.5; // 50%

        $quizScore = $this->quiz_average ?? 0;
        $freeResponseScore = $this->free_response_score ?? 0;
        $finalExamScore = $this->final_exam_score ?? 0;

        $overallScore = ($quizScore * $quizWeight) + 
                       ($freeResponseScore * $freeResponseWeight) + 
                       ($finalExamScore * $finalExamWeight);

        return round($overallScore, 2);
    }

    /**
     * Get grade letter based on overall score
     */
    public function getGradeLetter()
    {
        $score = $this->overall_score;

        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    /**
     * Check if grading period is still active
     */
    public function getIsGradingPeriodActiveAttribute()
    {
        return !$this->grading_completed && Carbon::now()->lt($this->grading_period_ends_at);
    }

    /**
     * Get remaining grading time
     */
    public function getRemainingGradingTimeAttribute()
    {
        if ($this->grading_completed) {
            return 'Grading completed';
        }

        $remaining = Carbon::now()->diffInHours($this->grading_period_ends_at, false);
        
        if ($remaining <= 0) {
            return 'Grading period expired';
        }

        return $remaining . ' hours remaining';
    }

    /**
     * Get formatted exam duration
     */
    public function getFormattedExamDurationAttribute()
    {
        $minutes = $this->exam_duration_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        } else {
            return "{$mins}m";
        }
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'passed' => 'success',
            'failed' => 'danger',
            'under_review' => 'warning',
            'pending' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Scope for passing results
     */
    public function scopePassing($query)
    {
        return $query->where('is_passing', true);
    }

    /**
     * Scope for results needing grading
     */
    public function scopeNeedingGrading($query)
    {
        return $query->where('grading_completed', false)
                    ->where('grading_period_ends_at', '>', Carbon::now());
    }

    /**
     * Scope for expired grading period
     */
    public function scopeGradingExpired($query)
    {
        return $query->where('grading_completed', false)
                    ->where('grading_period_ends_at', '<=', Carbon::now());
    }
}