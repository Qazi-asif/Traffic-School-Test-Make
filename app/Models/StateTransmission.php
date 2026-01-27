<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StateTransmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'state',
        'system',
        'status',
        'payload_json',
        'response_code',
        'response_message',
        'sent_at',
        'retry_count',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Get the enrollment that owns the transmission.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    /**
     * Scope for pending transmissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for error transmissions.
     */
    public function scopeError($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope for successful transmissions.
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for specific state.
     */
    public function scopeForState($query, string $state)
    {
        return $query->where('state', strtoupper($state));
    }
}
