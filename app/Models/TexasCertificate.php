<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TexasCertificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'certificate_number',
        'student_name',
        'course_name',
        'completion_date',
        'final_exam_score',
        'defensive_driving_hours',
        'tdlr_course_id',
        'approval_number',
        'student_address',
        'student_date_of_birth',
        'driver_license_number',
        'citation_number',
        'court_name',
        'citation_county',
        'traffic_school_due_date',
        'verification_hash',
        'pdf_path',
        'is_sent_to_student',
        'sent_at',
        'is_sent_to_state',
        'state_submission_date',
        'generated_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'student_date_of_birth' => 'date',
        'traffic_school_due_date' => 'date',
        'final_exam_score' => 'decimal:2',
        'defensive_driving_hours' => 'integer',
        'is_sent_to_student' => 'boolean',
        'sent_at' => 'datetime',
        'is_sent_to_state' => 'boolean',
        'state_submission_date' => 'datetime',
        'generated_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Certificate statuses
    const STATUS_GENERATED = 'generated';
    const STATUS_SENT = 'sent';
    const STATUS_VERIFIED = 'verified';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_FAILED = 'failed';

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

    public function stateTransmissions()
    {
        return $this->morphMany(StateTransmission::class, 'certificate');
    }

    /**
     * Check if certificate meets Texas TDLR requirements
     */
    public function meetsTexasRequirements(): bool
    {
        return $this->final_exam_score >= 75 && 
               $this->defensive_driving_hours >= 6 && 
               !empty($this->driver_license_number) && 
               !empty($this->completion_date);
    }

    /**
     * Get TDLR submission data
     */
    public function getTdlrSubmissionDataAttribute(): array
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
            'course_hours' => $this->defensive_driving_hours,
            'tdlr_course_id' => $this->tdlr_course_id,
            'citation_number' => $this->citation_number,
            'court_name' => $this->court_name,
            'county' => $this->citation_county,
            'school_name' => config('app.name'),
        ];
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
     * Scope for state-submitted certificates
     */
    public function scopeSubmittedToState($query)
    {
        return $query->where('is_sent_to_state', true);
    }

    /**
     * Scope for TDLR approved courses
     */
    public function scopeTdlrApproved($query)
    {
        return $query->whereNotNull('tdlr_course_id');
    }
}