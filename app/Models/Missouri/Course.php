<?php

namespace App\Models\Missouri;

use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $table = 'missouri_courses';

    protected $fillable = [
        'course_id',
        'missouri_course_code',
        'course_type',
        'form_4444_template',
        'requires_form_4444',
        'required_hours',
        'max_completion_days',
        'approval_number',
        'approved_date',
        'expiration_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_form_4444' => 'boolean',
        'required_hours' => 'decimal:2',
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
            ->where('course_table', 'missouri_courses');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'course_id');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'course_id');
    }

    public function form4444s(): HasMany
    {
        return $this->hasMany(\App\Models\MissouriForm4444::class, 'course_id');
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