<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCourse extends Model
{
    protected $table = 'florida_courses';

    protected $fillable = [
        'title',
        'description',
        'state_code',
        'course_type',
        'delivery_type',
        'price',
        'min_pass_score',
        'total_duration',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_pass_score' => 'integer',
        'total_duration' => 'integer',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(FloridaSchool::class, 'school_id');
    }
}
