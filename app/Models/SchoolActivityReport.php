<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolActivityReport extends Model
{
    protected $fillable = [
        'report_date',
        'date_range_start',
        'date_range_end',
        'school_id',
        'course_type',
        'certificates_issued',
        'generated_by',
        'report_data',
    ];

    protected $casts = [
        'report_date' => 'date',
        'date_range_start' => 'date',
        'date_range_end' => 'date',
        'report_data' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(FloridaSchool::class, 'school_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
