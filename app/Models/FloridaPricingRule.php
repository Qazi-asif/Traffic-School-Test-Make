<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaPricingRule extends Model
{
    protected $fillable = [
        'course_type', 'delivery_type', 'base_price', 'florida_assessment_fee',
        'is_active', 'effective_date', 'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
        'base_price' => 'decimal:2',
        'florida_assessment_fee' => 'decimal:2',
    ];
}
