<?php

namespace App\Models\Delaware;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $table = 'delaware_certificates';

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
        'dmv_submitted',
        'dmv_submitted_at',
        'dmv_response',
        'insurance_discount_certificate',
        'aggressive_driving_certificate',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'traffic_school_due_date' => 'date',
        'student_date_of_birth' => 'date',
        'final_exam_score' => 'decimal:2',
        'is_sent_to_student' => 'boolean',
        'dmv_submitted' => 'boolean',
        'insurance_discount_certificate' => 'boolean',
        'aggressive_driving_certificate' => 'boolean',
        'sent_at' => 'datetime',
        'generated_at' => 'datetime',
        'dmv_submitted_at' => 'datetime',
        'dmv_response' => 'array',
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

    public function scopeSubmittedToDmv($query)
    {
        return $query->where('dmv_submitted', true);
    }

    public function scopeInsuranceDiscount($query)
    {
        return $query->where('insurance_discount_certificate', true);
    }

    public function scopeAggressiveDriving($query)
    {
        return $query->where('aggressive_driving_certificate', true);
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
            'state' => 'delaware'
        ]);
    }

    public function getStatusAttribute()
    {
        if ($this->dmv_submitted) {
            return 'Submitted to DMV';
        }
        
        if ($this->is_sent_to_student) {
            return 'Sent to Student';
        }
        
        if ($this->generated_at) {
            return 'Generated';
        }
        
        return 'Pending';
    }

    public function getCertificateTypeAttribute()
    {
        $types = [];
        
        if ($this->aggressive_driving_certificate) {
            $types[] = 'Aggressive Driving';
        }
        
        if ($this->insurance_discount_certificate) {
            $types[] = 'Insurance Discount';
        }
        
        if (empty($types)) {
            $types[] = 'Standard Defensive Driving';
        }
        
        return implode(', ', $types);
    }
}