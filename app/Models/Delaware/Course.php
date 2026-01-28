<?php

namespace App\Models\Delaware;

use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'delaware_courses';

    protected $fillable = [
        'title',
        'description',
        'course_details',
        'state_code',
        'min_pass_score',
        'total_duration',
        'price',
        'dmv_course_id',
        'certificate_template',
        'is_active',
        'course_type',
        'delivery_type',
        'quiz_rotation_enabled',
        'aggressive_driving_course',
        'insurance_discount_eligible',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quiz_rotation_enabled' => 'boolean',
        'aggressive_driving_course' => 'boolean',
        'insurance_discount_eligible' => 'boolean',
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