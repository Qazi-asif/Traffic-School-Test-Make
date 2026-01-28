<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'course_table',
        'amount',
        'currency',
        'status',
        'payment_method',
        'gateway_payment_id',
        'gateway_customer_id',
        'gateway_transaction_id',
        'gateway_fee',
        'metadata',
        'paid_at',
        'error_message',
        'refunded_at',
        'refund_amount',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(UserCourseEnrollment::class, 'payment_id');
    }

    // Dynamic course relationship based on course_table
    public function course()
    {
        switch ($this->course_table) {
            case 'florida_courses':
                return $this->belongsTo(FloridaCourse::class, 'course_id');
            case 'missouri_courses':
                return $this->belongsTo(\App\Models\Missouri\Course::class, 'course_id');
            case 'texas_courses':
                return $this->belongsTo(\App\Models\Texas\Course::class, 'course_id');
            case 'delaware_courses':
                return $this->belongsTo(\App\Models\Delaware\Course::class, 'course_id');
            default:
                return $this->belongsTo(Course::class, 'course_id');
        }
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->whereNotNull('refunded_at');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedGatewayFeeAttribute()
    {
        return '$' . number_format($this->gateway_fee, 2);
    }

    public function getNetAmountAttribute()
    {
        return $this->amount - $this->gateway_fee;
    }

    public function getFormattedNetAmountAttribute()
    {
        return '$' . number_format($this->getNetAmountAttribute(), 2);
    }

    public function getIsRefundableAttribute()
    {
        return $this->status === 'completed' && !$this->refunded_at;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'completed' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Methods
    public function markAsCompleted($transactionId = null, $gatewayFee = 0)
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'gateway_transaction_id' => $transactionId,
            'gateway_fee' => $gatewayFee,
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function processRefund($amount = null, $reason = null)
    {
        $refundAmount = $amount ?? $this->amount;
        
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_amount' => $refundAmount,
            'refund_reason' => $reason,
        ]);

        // Update related enrollment if exists
        if ($this->enrollment) {
            $this->enrollment->update([
                'payment_status' => 'refunded',
                'status' => 'cancelled',
                'access_revoked' => true,
                'access_revoked_at' => now(),
            ]);
        }
    }

    public function getCourseData()
    {
        switch ($this->course_table) {
            case 'florida_courses':
                return \Illuminate\Support\Facades\DB::table('florida_courses')->where('id', $this->course_id)->first();
            case 'missouri_courses':
                return \Illuminate\Support\Facades\DB::table('missouri_courses')->where('id', $this->course_id)->first();
            case 'texas_courses':
                return \Illuminate\Support\Facades\DB::table('texas_courses')->where('id', $this->course_id)->first();
            case 'delaware_courses':
                return \Illuminate\Support\Facades\DB::table('delaware_courses')->where('id', $this->course_id)->first();
            default:
                return \Illuminate\Support\Facades\DB::table('courses')->where('id', $this->course_id)->first();
        }
    }
}