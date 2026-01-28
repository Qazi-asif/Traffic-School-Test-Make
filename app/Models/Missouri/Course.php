<?php

namespace App\Models\Missouri;

use App\Models\UserCourseEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'missouri_courses';

    protected $fillable = [
        'title',
        'description',
        'course_details',
        'state_code',
        'min_pass_score',
        'total_duration',
        'price',
        'form_4444_template',
        'is_active',
        'course_type',
        'delivery_type',
        'requires_form_4444',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_form_4444' => 'boolean',
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