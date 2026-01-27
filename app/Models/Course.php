<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'state',
        'duration',
        'price',
        'passing_score',
        'is_active',
        'course_type',
        'delivery_type',
        'certificate_type',
    ];

    const COURSE_TYPES = ['BDI', 'ADI', 'TLSAE'];

    const DELIVERY_TYPES = ['In Person', 'Internet', 'CD-Rom', 'Video', 'DVD'];

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function schoolCourses()
    {
        return $this->hasMany(SchoolCourse::class);
    }

    public function instructorCourses()
    {
        return $this->hasMany(InstructorCourse::class);
    }

    public function enrollments()
    {
        return $this->hasMany(UserCourseEnrollment::class, 'course_id')->where('course_table', 'courses');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($course) {
            // Delete all related enrollments
            $course->enrollments()->delete();
            
            // Delete related chapters
            $course->chapters()->delete();
            
            // Delete related school courses
            $course->schoolCourses()->delete();
            
            // Delete related instructor courses
            $course->instructorCourses()->delete();
        });
    }
}

class SchoolCourse extends Model
{
    protected $table = 'dicds_school_courses';

    protected $fillable = ['school_id', 'course_id', 'status', 'status_date'];

    protected $casts = ['status_date' => 'date'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
