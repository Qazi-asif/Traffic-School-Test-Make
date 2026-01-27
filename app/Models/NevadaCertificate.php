<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NevadaCertificate extends Model
{
    protected $fillable = [
        'certificate_id',
        'enrollment_id',
        'nevada_certificate_number',
        'completion_date',
        'submission_status',
        'submission_date',
        'submission_response',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'submission_date' => 'datetime',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(NevadaSubmission::class, 'certificate_id');
    }

    public function scopePending($query)
    {
        return $query->where('submission_status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('submission_status', 'submitted');
    }

    public function scopeAccepted($query)
    {
        return $query->where('submission_status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('submission_status', 'rejected');
    }
}
