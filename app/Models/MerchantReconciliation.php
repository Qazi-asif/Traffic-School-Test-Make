<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantReconciliation extends Model
{
    protected $fillable = [
        'merchant_account_id', 'period_start', 'period_end',
        'expected_revenue', 'actual_revenue', 'expected_fees', 'actual_fees',
        'discrepancy_amount', 'status', 'notes', 'reconciled_by', 'reconciled_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'expected_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'expected_fees' => 'decimal:2',
        'actual_fees' => 'decimal:2',
        'discrepancy_amount' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    public function merchantAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantAccount::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function scopeDiscrepancy($query)
    {
        return $query->where('status', 'discrepancy');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
