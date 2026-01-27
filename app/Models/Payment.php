<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'gateway',
        'gateway_payment_id',
        'intent_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'billing_name',
        'billing_email',
        'billing_address',
        'address',
        'city',
        'state',
        'country',
        'zipcode',
        'refund_reason',
        'refunded_at',
        'metadata',
        'coupon_code',
        'discount_amount',
        'original_amount',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
