<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaEmailLog extends Model
{
    protected $fillable = [
        'template_id', 'enrollment_id', 'recipient_email', 'recipient_name', 'subject', 'content',
        'florida_variables_used', 'dicds_reference', 'status', 'gateway_message_id', 'gateway_response',
        'opened_at', 'delivered_at', 'sent_at',
    ];

    protected $casts = [
        'florida_variables_used' => 'array',
        'gateway_response' => 'array',
        'opened_at' => 'datetime',
        'delivered_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(FloridaEmailTemplate::class, 'template_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }
}
