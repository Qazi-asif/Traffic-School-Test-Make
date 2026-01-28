<?php

namespace App\Models\Delaware;

use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $table = 'delaware_courses';

    protected $fillable = [
        'course_id',
        'delaware_course_code',
        'course_type',
        'required_hours',
        'max_completion_days',
        'approval_number',
        'approved_date',
        'expiration_date',
        'certificate_template',
        'quiz_rotation_enabled',
        'quiz_pool_size',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quiz_rotation_enabled' => 'boolean',
        'required_hours' => 'decimal:2',
        'quiz_pool_size' => 'integer',
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
            ->where('course_table', 'delaware_courses');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'course_id');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'course_id');
    }

    public function scopeAggressiveDriving($query)
    {
        return $query->where('aggressive_driving_course', true);
    }

    public function scopeInsuranceDiscount($query)
    {
        return $query->where('insurance_discount_eligible', true);
    }

    public function scopeQuizRotationEnabled($query)
    {
        return $query->where('quiz_rotation_enabled', true);
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