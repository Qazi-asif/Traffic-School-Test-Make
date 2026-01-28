<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DelawareCertificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'certificate_number',
        'student_name',
        'course_name',
        'completion_date',
        'final_exam_score',
        'course_duration_type',
        'required_hours',
        'approval_number',
        'quiz_rotation_enabled',
        'student_address',
        'student_date_of_birth',
        'driver_license_number',
        'verification_hash',
        'pdf_path',
        'is_sent_to_student',
        'sent_at',
        'generated_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'student_date_of_birth' => 'date',
        'final_exam_score' => 'decimal:2',
        'required_hours' => 'integer',
        'quiz_rotation_enabled' => 'boolean',
        'is_sent_to_student' => 'boolean',
        'sent_at' => 'datetime',
        'generated_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Certificate statuses
    const STATUS_GENERATED = 'generated';
    const STATUS_SENT = 'sent';
    const STATUS_VERIFIED = 'verified';
    const STATUS_FAILED = 'failed';

    // Course duration types
    const DURATION_3HR = '3hr';
    const DURATION_6HR = '6hr';

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, UserCourseEnrollment::class, 'id', 'id', 'enrollment_id', 'user_id');
    }

    public function verificationLogs()
    {
        return $this->morphMany(CertificateVerificationLog::class, 'certificate');
    }

    /**
     * Check if certificate meets Delaware requirements
     */
    public function meetsDelawareRequirements(): bool
    {
        $minHours = $this->course_duration_type === self::DURATION_3HR ? 3 : 6;
        
        return $this->final_exam_score >= 80 && 
               $this->required_hours >= $minHours && 
               !empty($this->completion_date);
    }

    /**
     * Get course type description
     */
    public function getCourseTypeDescriptionAttribute(): string
    {
        switch ($this->course_duration_type) {
            case self::DURATION_3HR:
                return '3-Hour Point Reduction Course';
            case self::DURATION_6HR:
                return '6-Hour Insurance Discount Course';
            default:
                return '6-Hour Defensive Driving Course';
        }
    }

    /**
     * Get Delaware DMV submission data
     */
    public function getDmvSubmissionDataAttribute(): array
    {
        $enrollment = $this->enrollment;
        $user = $enrollment->user;

        return [
            'certificate_number' => $this->certificate_number,
            'student_name' => $this->student_name,
            'driver_license_number' => $this->driver_license_number,
            'date_of_birth' => $this->student_date_of_birth ? $this->student_date_of_birth->format('Y-m-d') : '',
            'completion_date' => $this->completion_date->format('Y-m-d'),
            'final_exam_score' => $this->final_exam_score,
            'course_type' => $this->course_duration_type,
            'course_hours' => $this->required_hours,
            'quiz_rotation_used' => $this->quiz_rotation_enabled,
            'school_name' => config('app.name'),
        ];
    }

    /**
     * Scope for 3-hour courses
     */
    public function scopeThreeHour($query)
    {
        return $query->where('course_duration_type', self::DURATION_3HR);
    }

    /**
     * Scope for 6-hour courses
     */
    public function scopeSixHour($query)
    {
        return $query->where('course_duration_type', self::DURATION_6HR);
    }

    /**
     * Scope for sent certificates
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent_to_student', true);
    }

    /**
     * Scope for pending certificates
     */
    public function scopePending($query)
    {
        return $query->where('is_sent_to_student', false);
    }

    /**
     * Scope for quiz rotation enabled
     */
    public function scopeWithQuizRotation($query)
    {
        return $query->where('quiz_rotation_enabled', true);
    }
}