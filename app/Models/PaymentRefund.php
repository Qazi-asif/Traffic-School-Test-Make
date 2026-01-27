<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    protected $fillable = [
        'payment_id', 'refund_amount', 'refund_reason', 'refund_description',
        'gateway_refund_id', 'status', 'processed_by', 'processed_at', 'florida_fee_refunded',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'florida_fee_refunded' => 'boolean',
        'refund_amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(FloridaPayment::class, 'payment_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
