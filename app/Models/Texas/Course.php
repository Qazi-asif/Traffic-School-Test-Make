<?php

namespace App\Models\Texas;

use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'texas_courses';

    protected $fillable = [
        'title',
        'description',
        'course_details',
        'state_code',
        'min_pass_score',
        'total_duration',
        'price',
        'tdlr_course_id',
        'certificate_template',
        'is_active',
        'course_type',
        'delivery_type',
        'requires_proctoring',
        'defensive_driving_hours',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_proctoring' => 'boolean',
        'price' => 'decimal:2',
        'min_pass_score' => 'integer',
        'total_duration' => 'integer',
        'defensive_driving_hours' => 'integer',
    ];

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'course_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(UserCourseEnrollment::class, 'course_id')
            ->where('course_table', 'texas_courses');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'course_id');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'course_id');
    }

    public function scopeDefensiveDriving($query)
    {
        return $query->where('course_type', 'defensive_driving');
    }

    public function scopeRequiresProctoring($query)
    {
        return $query->where('requires_proctoring', true);
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