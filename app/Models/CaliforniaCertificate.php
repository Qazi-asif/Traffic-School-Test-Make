<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaliforniaCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'certificate_number',
        'cc_seq_nbr',
        'cc_stat_cd',
        'cc_sub_tstamp',
        'court_code',
        'student_name',
        'completion_date',
        'driver_license',
        'birth_date',
        'citation_number',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'birth_date' => 'date',
        'cc_sub_tstamp' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
