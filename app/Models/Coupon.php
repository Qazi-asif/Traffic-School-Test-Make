<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'amount',
        'type',
        'is_active',
        'is_used',
        'expires_at',
        'usage_limit',
        'used_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_used' => 'boolean',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
    ];

    public function usage()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid()
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->is_used) {
            return false;
        }
        if ($this->usage_limit && $this->usage->count() >= $this->usage_limit) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function calculateDiscount($amount)
    {
        if ($this->type === 'percentage') {
            return ($amount * $this->amount) / 100;
        }

        return min($this->amount, $amount);
    }

    public static function generateCode()
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function incrementUsage()
    {
        $actualCount = $this->usage->count();
        $this->update(['used_count' => $actualCount]);
        
        if ($this->usage_limit && $actualCount >= $this->usage_limit) {
            $this->update(['is_used' => true]);
        }
    }

    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }
}
