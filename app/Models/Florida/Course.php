<?php

namespace App\Models\Florida;

use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'florida_courses';

    protected $fillable = [
        'title',
        'description',
        'course_details',
        'state_code',
        'min_pass_score',
        'total_duration',
        'price',
        'dicds_course_id',
        'certificate_template',
        'copyright_protected',
        'is_active',
        'course_type',
        'delivery_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'copyright_protected' => 'boolean',
        'price' => 'decimal:2',
        'min_pass_score' => 'integer',
        'total_duration' => 'integer',
    ];

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'course_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(UserCourseEnrollment::class, 'course_id')
            ->where('course_table', 'florida_courses');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'course_id');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'course_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($course) {
            $course->enrollments()->delete();
            $course->chapters()->delete();
            $course->certificates()->delete();
            $course->quizQuestions()->delete();
        });
    }
}