<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloridaCertificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'dicds_certificate_number',
        'student_name',
        'completion_date',
        'course_name',
        'final_exam_score',
        'driver_license_number',
        'citation_number',
        'citation_county',
        'traffic_school_due_date',
        'student_address',
        'student_date_of_birth',
        'court_name',
        'state',
        'pdf_path',
        'verification_hash',
        'is_sent_to_student',
        'sent_at',
        'generated_at',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'traffic_school_due_date' => 'date',
        'student_date_of_birth' => 'date',
        'final_exam_score' => 'decimal:2',
        'is_sent_to_student' => 'boolean',
        'sent_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

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
        return $this->hasMany(CertificateVerificationLog::class, 'certificate_id');
    }
}
