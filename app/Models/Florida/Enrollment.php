<?php

namespace App\Models\Florida;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $table = 'florida_enrollments';

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
        'final_exam_result_id',
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
        'access_revoked' => 'boolean',
        'final_exam_completed' => 'boolean',
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

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}