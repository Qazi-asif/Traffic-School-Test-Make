<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DicdsWebServiceInfo extends Model
{
    protected $table = 'dicds_web_service_info';

    protected $fillable = [
        'school_id',
        'course_assignments',
        'instructor_assignments',
        'last_updated',
    ];

    protected $casts = [
        'course_assignments' => 'array',
        'instructor_assignments' => 'array',
        'last_updated' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(FloridaSchool::class, 'school_id');
    }
}
