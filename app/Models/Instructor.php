<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $table = 'dicds_instructors';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone',
        'address', 'city', 'state', 'zip_code', 'school_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function courses()
    {
        return $this->hasMany(InstructorCourse::class);
    }
}

class InstructorCourse extends Model
{
    protected $fillable = ['instructor_id', 'course_id', 'status', 'status_date'];

    protected $casts = ['status_date' => 'date'];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
