<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantAccount extends Model
{
    protected $fillable = [
        'gateway_id', 'account_name', 'account_identifier', 'account_email',
        'is_primary', 'is_active', 'currency', 'payout_schedule', 'payout_day',
        'minimum_payout', 'reserve_percent', 'metadata', 'last_payout_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'minimum_payout' => 'decimal:2',
        'reserve_percent' => 'decimal:2',
        'metadata' => 'array',
        'last_payout_at' => 'datetime',
    ];

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MerchantTransaction::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(MerchantPayout::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(MerchantFee::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(MerchantReconciliation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function getBalanceAttribute(): float
    {
        return $this->transactions()
            ->where('status', 'completed')
            ->sum('net_amount');
    }

    public function getPendingPayoutAttribute(): float
    {
        return $this->transactions()
            ->where('status', 'completed')
            ->whereNull('settled_at')
            ->sum('net_amount');
    }
}
