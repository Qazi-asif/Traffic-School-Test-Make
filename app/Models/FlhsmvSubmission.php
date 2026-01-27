<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlhsmvSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'florida_certificate_id',
        'submission_data',
        'response_data',
        'status',
        'error_code',
        'error_message',
        'retry_count',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'submission_data' => 'array',
        'response_data' => 'array',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificate()
    {
        return $this->belongsTo(FloridaCertificate::class, 'florida_certificate_id');
    }

    public function errors()
    {
        return $this->hasMany(FlhsmvSubmissionError::class);
    }
}
