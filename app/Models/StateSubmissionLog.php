<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateSubmissionLog extends Model
{
    protected $fillable = [
        'certificate_id',
        'submission_method',
        'submitted_data',
        'response_data',
        'status_code',
        'status_message',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_data' => 'array',
        'response_data' => 'array',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
