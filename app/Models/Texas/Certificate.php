<?php

namespace App\Models\Texas;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $table = 'texas_certificates';

    protected $fillable = [
        'enrollment_id',
        'certificate_number',
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
        'tdlr_submitted',
        'tdlr_submitted_at',
        'tdlr_response',
        'proctoring_verified',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'traffic_school_due_date' => 'date',
        'student_date_of_birth' => 'date',
        'final_exam_score' => 'decimal:2',
        'is_sent_to_student' => 'boolean',
        'tdlr_submitted' => 'boolean',
        'proctoring_verified' => 'boolean',
        'sent_at' => 'datetime',
        'generated_at' => 'datetime',
        'tdlr_submitted_at' => 'datetime',
        'tdlr_response' => 'array',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Enrollment::class,
            'id',
            'id',
            'enrollment_id',
            'user_id'
        );
    }

    public function scopeGenerated($query)
    {
        return $query->whereNotNull('generated_at');
    }

    public function scopeSent($query)
    {
        return $query->where('is_sent_to_student', true);
    }

    public function scopeSubmittedToTdlr($query)
    {
        return $query->where('tdlr_submitted', true);
    }

    public function scopeProctoringVerified($query)
    {
        return $query->where('proctoring_verified', true);
    }

    public function generateVerificationHash()
    {
        $data = $this->certificate_number . 
                $this->student_name . 
                $this->completion_date->format('Y-m-d');
        
        return hash('sha256', $data);
    }

    public function getDownloadUrlAttribute()
    {
        if (!$this->pdf_path) {
            return null;
        }
        
        return route('certificates.download', [
            'certificate' => $this->id,
            'hash' => $this->verification_hash,
            'state' => 'texas'
        ]);
    }

    public function getStatusAttribute()
    {
        if ($this->tdlr_submitted) {
            return 'Submitted to TDLR';
        }
        
        if ($this->is_sent_to_student) {
            return 'Sent to Student';
        }
        
        if ($this->proctoring_verified) {
            return 'Proctoring Verified';
        }
        
        if ($this->generated_at) {
            return 'Generated';
        }
        
        return 'Pending';
    }
}