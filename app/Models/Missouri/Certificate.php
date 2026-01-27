<?php

namespace App\Models\Missouri;

use App\Models\User;
use App\Models\MissouriForm4444;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Certificate extends Model
{
    protected $table = 'missouri_certificates';

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
        'form_4444_attached',
        'form_4444_generated_at',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'traffic_school_due_date' => 'date',
        'student_date_of_birth' => 'date',
        'final_exam_score' => 'decimal:2',
        'is_sent_to_student' => 'boolean',
        'form_4444_attached' => 'boolean',
        'sent_at' => 'datetime',
        'generated_at' => 'datetime',
        'form_4444_generated_at' => 'datetime',
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

    public function form4444(): HasOne
    {
        return $this->hasOne(MissouriForm4444::class, 'certificate_id');
    }

    public function scopeGenerated($query)
    {
        return $query->whereNotNull('generated_at');
    }

    public function scopeSent($query)
    {
        return $query->where('is_sent_to_student', true);
    }

    public function scopeWithForm4444($query)
    {
        return $query->where('form_4444_attached', true);
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
            'state' => 'missouri'
        ]);
    }

    public function getStatusAttribute()
    {
        if ($this->is_sent_to_student) {
            return 'Sent to Student';
        }
        
        if ($this->form_4444_attached) {
            return 'Form 4444 Attached';
        }
        
        if ($this->generated_at) {
            return 'Generated';
        }
        
        return 'Pending';
    }
}