<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaPayment extends Model
{
    protected $fillable = [
        'user_id', 'enrollment_id', 'course_type', 'delivery_type', 'base_course_price',
        'florida_assessment_fee', 'convenience_fee', 'total_amount', 'payment_gateway',
        'gateway_payment_id', 'gateway_intent_id', 'payment_status', 'payment_method',
        'billing_name', 'billing_email', 'billing_address', 'florida_fee_remitted',
        'florida_fee_remittance_date', 'florida_remittance_reference', 'refund_reason',
        'refunded_at', 'metadata',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'metadata' => 'array',
        'florida_fee_remitted' => 'boolean',
        'florida_fee_remittance_date' => 'date',
        'refunded_at' => 'datetime',
        'base_course_price' => 'decimal:2',
        'florida_assessment_fee' => 'decimal:2',
        'convenience_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class, 'payment_id');
    }
}
