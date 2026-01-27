<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DicdsCertificateOrder extends Model
{
    protected $fillable = [
        'school_id',
        'course_id',
        'certificate_count',
        'total_amount',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(FloridaSchool::class, 'school_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }
}
