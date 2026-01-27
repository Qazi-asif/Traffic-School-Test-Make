<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'certificate_number',
        'student_name',
        'completion_date',
        'course_name',
        'state_code',
        'issued_at',
        'pdf_path',
        'verification_hash',
        'is_sent_to_state',
        'state_submission_id',
        'submission_attempts',
        'last_submission_attempt',
        'status',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'issued_at' => 'datetime',
        'last_submission_attempt' => 'datetime',
        'is_sent_to_state' => 'boolean',
    ];

    public function enrollment()
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, UserCourseEnrollment::class, 'id', 'id', 'enrollment_id', 'user_id');
    }

    public function submissionLogs()
    {
        return $this->hasMany(StateSubmissionLog::class);
    }
}
