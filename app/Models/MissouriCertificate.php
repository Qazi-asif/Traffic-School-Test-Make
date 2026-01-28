<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissouriCertificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'certificate_number',
        'student_name',
        'course_name',
        'completion_date',
        'final_exam_score',
        'required_hours',
        'approval_number',
        'requires_form_4444',
        'form_4444_number',
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
        'requires_form_4444' => 'boolean',
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
     * Generate Missouri Form 4444 data
     */
    public function getForm4444DataAttribute(): array
    {
        $enrollment = $this->enrollment;
        $user = $enrollment->user;

        return [
            'form_number' => $this->form_4444_number ?: 'MO-4444-' . $this->id,
            'student_name' => $this->student_name,
            'student_address' => $this->student_address ?: $user->mailing_address,
            'student_city' => $user->city ?? '',
            'student_state' => $user->state ?? 'MO',
            'student_zip' => $user->zip ?? '',
            'drivers_license' => $this->driver_license_number ?: $user->driver_license,
            'date_of_birth' => $this->student_date_of_birth ? $this->student_date_of_birth->format('m/d/Y') : '',
            'course_completion_date' => $this->completion_date->format('m/d/Y'),
            'course_name' => $this->course_name,
            'approval_number' => $this->approval_number,
            'hours_completed' => $this->required_hours,
            'school_name' => config('app.name', 'Traffic School'),
        ];
    }

    /**
     * Check if certificate meets Missouri requirements
     */
    public function meetsMissouriRequirements(): bool
    {
        return $this->final_exam_score >= 70 && 
               $this->required_hours >= 8 && 
               !empty($this->student_address) && 
               !empty($this->completion_date);
    }

    /**
     * Scope for certificates requiring Form 4444
     */
    public function scopeRequiresForm4444($query)
    {
        return $query->where('requires_form_4444', true);
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
}