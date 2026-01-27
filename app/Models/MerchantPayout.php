<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantPayout extends Model
{
    protected $fillable = [
        'merchant_account_id', 'payout_reference', 'amount', 'currency',
        'status', 'initiated_at', 'expected_arrival_at', 'arrived_at',
        'bank_account_last4', 'failure_reason', 'transaction_ids',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'expected_arrival_at' => 'date',
        'arrived_at' => 'datetime',
        'transaction_ids' => 'array',
    ];

    public function merchantAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantAccount::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
