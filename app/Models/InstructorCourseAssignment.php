<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorCourseAssignment extends Model
{
    protected $fillable = [
        'instructor_id',
        'course_type',
        'delivery_type',
        'status',
        'status_date',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'status_date' => 'date',
        'assigned_at' => 'datetime',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(FloridaInstructor::class, 'instructor_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
