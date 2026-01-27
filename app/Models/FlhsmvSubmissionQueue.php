<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlhsmvSubmissionQueue extends Model
{
    protected $fillable = [
        'flhsmv_submission_id',
        'priority',
        'scheduled_at',
        'attempts',
        'last_attempt_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(FlhsmvSubmission::class);
    }
}
