<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCourseEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'course_table',
        'payment_status',
        'amount_paid',
        'payment_method',
        'payment_id',
        'citation_number',
        'case_number',
        'court_state',
        'court_county',
        'court_selected',
        'court_date',
        'enrolled_at',
        'started_at',
        'completed_at',
        'progress_percentage',
        'quiz_average',
        'total_time_spent',
        'status',
        'access_revoked',
        'access_revoked_at',
        'last_activity_at',
        'reminder_sent_at',
        'reminder_count',
        'optional_services',
        'optional_services_total',
        'final_exam_completed',
        'final_exam_result_id',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'court_date' => 'date',
        'amount_paid' => 'decimal:2',
        'optional_services_total' => 'decimal:2',
        'optional_services' => 'array',
        'payment_status' => 'string',
        'status' => 'string',
        'last_activity_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'access_revoked_at' => 'datetime',
    ];

    // Performance optimization: Always eager load these relationships
    protected $with = ['user'];

    // Cache frequently accessed data
    public function getCourseAttribute()
    {
        return cache()->remember(
            "enrollment_course_{$this->id}_{$this->course_table}_{$this->course_id}",
            300, // 5 minutes
            function () {
                if ($this->course_table === 'florida_courses') {
                    return \App\Models\FloridaCourse::find($this->course_id);
                }
                return \App\Models\Course::find($this->course_id);
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        // Dynamic relationship based on course_table field
        $table = $this->course_table ?? 'florida_courses';
        
        if ($table === 'courses') {
            return $this->belongsTo(Course::class, 'course_id');
        } else {
            return $this->belongsTo(FloridaCourse::class, 'course_id');
        }
    }

    public function floridaCourse(): BelongsTo
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }

    public function legacyCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Get course data regardless of table
    public function getCourseData()
    {
        $table = $this->course_table ?? 'florida_courses';
        
        if ($table === 'courses') {
            return \Illuminate\Support\Facades\DB::table('courses')->where('id', $this->course_id)->first();
        } else {
            return \Illuminate\Support\Facades\DB::table('florida_courses')->where('id', $this->course_id)->first();
        }
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class, 'enrollment_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'enrollment_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'enrollment_id');
    }

    public function floridaCertificate()
    {
        return $this->hasOne(FloridaCertificate::class, 'enrollment_id');
    }

    public function californiaCertificate()
    {
        return $this->hasOne(CaliforniaCertificate::class, 'enrollment_id');
    }

    public function ctsiResults()
    {
        return $this->hasMany(CtsiResult::class, 'enrollment_id');
    }

    public function stateTransmissions(): HasMany
    {
        return $this->hasMany(StateTransmission::class, 'enrollment_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'enrollment_id');
    }

    // Status-based scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereNull('completed_at')
            ->where('access_revoked', false);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // Segment scopes
    public function scopeCompletedInMonth($query, $year, $month)
    {
        return $query->whereNotNull('completed_at')
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month);
    }

    public function scopeCompletedInDateRange($query, $start, $end)
    {
        return $query->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$start, $end]);
    }

    public function scopePaidNotCompleted($query)
    {
        return $query->where('payment_status', 'paid')
            ->whereNull('completed_at')
            ->where('access_revoked', false);
    }

    public function scopeInProgressNotPaid($query)
    {
        return $query->where('payment_status', '!=', 'paid')
            ->whereNotNull('started_at')
            ->whereNull('completed_at');
    }

    public function scopeAbandoned($query, $daysInactive = 30)
    {
        $cutoffDate = now()->subDays($daysInactive);

        return $query->whereNull('completed_at')
            ->where(function ($q) use ($cutoffDate) {
                $q->where('last_activity_at', '<', $cutoffDate)
                    ->orWhere(function ($q2) use ($cutoffDate) {
                        $q2->whereNull('last_activity_at')
                            ->where('enrolled_at', '<', $cutoffDate);
                    });
            });
    }

    public function scopeExpiringWithin($query, $days = 7)
    {
        $futureDate = now()->addDays($days);

        return $query->whereNull('completed_at')
            ->where('status', '!=', 'expired')
            ->whereNotNull('court_date')
            ->whereBetween('court_date', [now(), $futureDate]);
    }

    public function scopeExpiredRecently($query, $days = 30)
    {
        $cutoffDate = now()->subDays($days);

        return $query->where('status', 'expired')
            ->where('updated_at', '>=', $cutoffDate);
    }

    public function scopeNeverStarted($query)
    {
        return $query->whereNull('started_at')
            ->whereNull('completed_at')
            ->where('payment_status', 'paid');
    }

    public function scopeStuckOnQuiz($query, $failedAttempts = 3)
{
    return $query->whereNull('completed_at')
        ->whereExists(function ($q) use ($failedAttempts) {
            $q->select('enrollment_id')
              ->from('quiz_attempts')
              ->whereColumn('user_course_enrollments.id', 'quiz_attempts.enrollment_id')
              ->where('passed', false)
              ->groupBy('enrollment_id')
              ->havingRaw('COUNT(*) >= ?', [$failedAttempts]);
        });
}

    public function scopeByState($query, $stateCode)
    {
        return $query->whereHas('course', function ($q) use ($stateCode) {
            $q->where('state', $stateCode);
        });
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Safely format a date field, handling both string and Carbon instances
     */
    public function formatDateSafely($field, $format = 'M d, Y')
    {
        $value = $this->$field;
        
        if (!$value) {
            return null;
        }
        
        try {
            if (is_string($value)) {
                return \Carbon\Carbon::parse($value)->format($format);
            } elseif ($value instanceof \Carbon\Carbon) {
                return $value->format($format);
            } else {
                return $value;
            }
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get formatted completion date
     */
    public function getFormattedCompletedAtAttribute()
    {
        return $this->formatDateSafely('completed_at');
    }

    /**
     * Get formatted access revoked date
     */
    public function getFormattedAccessRevokedAtAttribute()
    {
        return $this->formatDateSafely('access_revoked_at', 'M d, Y H:i');
    }
}
