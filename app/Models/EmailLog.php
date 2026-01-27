<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'template_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'content',
        'variables_used',
        'status',
        'gateway_message_id',
        'gateway_response',
        'opened_at',
        'delivered_at',
        'sent_at',
    ];

    protected $casts = [
        'variables_used' => 'array',
        'gateway_response' => 'array',
        'opened_at' => 'datetime',
        'delivered_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public $timestamps = false;

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }
}
