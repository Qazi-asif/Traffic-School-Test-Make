<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateSubmissionQueue extends Model
{
    protected $table = 'state_submission_queue';

    protected $fillable = [
        'certificate_id',
        'state_config_id',
        'priority',
        'attempts',
        'max_attempts',
        'last_attempt_at',
        'next_attempt_at',
        'status',
        'error_message',
        'submitted_data',
        'response_data',
        'processed_at',
    ];

    protected $casts = [
        'submitted_data' => 'array',
        'response_data' => 'array',
        'last_attempt_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function stateConfiguration()
    {
        return $this->belongsTo(StateConfiguration::class, 'state_config_id');
    }
}
