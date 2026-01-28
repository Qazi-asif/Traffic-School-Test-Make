<?php

namespace App\Models\Texas;

use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $table = 'texas_courses';

    protected $fillable = [
        'course_id',
        'texas_course_code',
        'tdlr_course_id',
        'course_type',
        'requires_proctoring',
        'defensive_driving_hours',
        'required_hours',
        'max_completion_days',
        'approval_number',
        'approved_date',
        'expiration_date',
        'certificate_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_proctoring' => 'boolean',
        'required_hours' => 'decimal:2',
        'defensive_driving_hours' => 'integer',
        'approved_date' => 'date',
        'expiration_date' => 'date',
    ];

    public function baseCourse(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

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