<?php

namespace App\Models\Texas;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $table = 'texas_enrollments';

    protected $fillable = [
        'user_id',
        'course_id',
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
        'final_exam_completed',
        'proctoring_required',
        'proctoring_completed',
        'proctoring_verified_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'court_date' => 'date',
        'amount_paid' => 'decimal:2',
        'payment_status' => 'string',
        'status' => 'string',
        'last_activity_at' => 'datetime',
        'access_revoked_at' => 'datetime',
        'proctoring_verified_at' => 'datetime',
        'access_revoked' => 'boolean',
        'final_exam_completed' => 'boolean',
        'proctoring_required' => 'boolean',
        'proctoring_completed' => 'boolean',
        'progress_percentage' => 'integer',
        'total_time_spent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class, 'enrollment_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'enrollment_id');
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'enrollment_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'enrollment_id');
    }

    // Scopes
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

    public function scopeRequiresProctoring($query)
    {
        return $query->where('proctoring_required', true);
    }

    public function scopeProctoringCompleted($query)
    {
        return $query->where('proctoring_completed', true);
    }

    public function canComplete()
    {
        if ($this->proctoring_required && !$this->proctoring_completed) {
            return false;
        }
        
        return $this->progress_percentage >= 100 && $this->final_exam_completed;
    }
}