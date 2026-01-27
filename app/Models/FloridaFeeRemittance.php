<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaFeeRemittance extends Model
{
    protected $fillable = [
        'remittance_date', 'total_assessment_fees', 'total_courses', 'payment_method',
        'florida_reference_number', 'submitted_by', 'submitted_at', 'processed_by_florida', 'processed_at',
    ];

    protected $casts = [
        'remittance_date' => 'date',
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
        'processed_by_florida' => 'boolean',
        'total_assessment_fees' => 'decimal:2',
    ];

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
