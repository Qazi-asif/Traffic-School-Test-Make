<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayWebhook extends Model
{
    protected $fillable = [
        'gateway_id',
        'webhook_url',
        'webhook_secret',
        'events',
        'is_active',
        'last_received_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_received_at' => 'datetime',
    ];

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }
}
