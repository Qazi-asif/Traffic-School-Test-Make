<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlhsmvSubmissionError extends Model
{
    protected $fillable = [
        'flhsmv_submission_id',
        'error_code',
        'error_message',
        'error_details',
        'occurred_at',
    ];

    protected $casts = [
        'error_details' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(FlhsmvSubmission::class);
    }
}
