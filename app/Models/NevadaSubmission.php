<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NevadaSubmission extends Model
{
    protected $fillable = [
        'certificate_id',
        'submission_method',
        'submission_date',
        'status',
        'confirmation_number',
        'response_data',
        'error_message',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'response_data' => 'array',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(NevadaCertificate::class, 'certificate_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'confirmed']);
    }
}
