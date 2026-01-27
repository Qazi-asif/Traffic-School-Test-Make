<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaCourse extends Model
{
    protected $fillable = [
        'title',
        'description',
        'course_details',
        'state_code',
        'min_pass_score',
        'duration',
        'price',
        'dicds_course_id',
        'certificate_template',
        'copyright_protected',
        'passing_score',
        'is_active',
        'course_type',
        'delivery_type',
        'certificate_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function chapters()
    {
        return $this->hasMany(CourseChapter::class, 'course_id');
    }

    public function courseChapters()
    {
        return $this->hasMany(CourseChapter::class, 'course_id');
    }

    public function enrollments()
    {
        return $this->hasMany(UserCourseEnrollment::class, 'course_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($course) {
            // Delete all related enrollments
            $course->enrollments()->delete();
            
            // Delete related chapters
            $course->chapters()->delete();
        });
    }
}
