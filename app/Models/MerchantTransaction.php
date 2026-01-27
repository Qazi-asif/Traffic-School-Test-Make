<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantTransaction extends Model
{
    protected $fillable = [
        'merchant_account_id', 'payment_id', 'transaction_type',
        'gross_amount', 'fee_amount', 'net_amount', 'currency',
        'gateway_transaction_id', 'status', 'description',
        'processed_at', 'settled_at', 'metadata',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'settled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function merchantAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantAccount::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnsettled($query)
    {
        return $query->whereNull('settled_at');
    }
}
