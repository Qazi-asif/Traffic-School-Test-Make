<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayPalPayment extends Model
{
    protected $fillable = [
        'payment_transaction_id',
        'paypal_order_id',
        'paypal_payer_id',
        'paypal_transaction_id',
        'status',
        'amount',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
