<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'state_code',
        'course_id',
        'course_table',
        'is_active',
        'is_required',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('display_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function course(): BelongsTo
    {
        // Dynamically determine which course table to use
        if ($this->course_table === 'florida_courses') {
            return $this->belongsTo(\App\Models\FloridaCourse::class, 'course_id');
        }
        
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the course regardless of which table it's in
     */
    public function getCourseAttribute()
    {
        if (!$this->course_id) {
            return null;
        }

        if ($this->course_table === 'florida_courses') {
            return \App\Models\FloridaCourse::find($this->course_id);
        }
        
        return Course::find($this->course_id);
    }

    /**
     * Get the course title for display
     */
    public function getCourseTitle()
    {
        if (!$this->course_id) {
            return 'All Courses';
        }

        if ($this->course_table === 'florida_courses') {
            $course = \App\Models\FloridaCourse::find($this->course_id);
            return $course ? $course->title . ' (Florida)' : 'Course Not Found';
        }
        
        $course = Course::find($this->course_id);
        return $course ? $course->title . ' (Regular)' : 'Course Not Found';
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeForState(Builder $query, ?string $stateCode): Builder
    {
        return $query->where(function ($q) use ($stateCode) {
            $q->whereNull('state_code')
                ->orWhere('state_code', $stateCode);
        });
    }

    public function scopeForCourse(Builder $query, ?int $courseId): Builder
    {
        return $query->where(function ($q) use ($courseId) {
            $q->whereNull('course_id')
                ->orWhere('course_id', $courseId);
        });
    }

    // Methods
    public static function getApplicableSurvey($enrollment): ?Survey
    {
        $stateCode = $enrollment->course->state_code ?? null;
        $courseId = $enrollment->course_id;

        // Priority: Course-specific > State-specific > General
        return self::active()
            ->required()
            ->where(function ($query) use ($stateCode, $courseId) {
                $query->where('course_id', $courseId)
                    ->orWhere(function ($q) use ($stateCode) {
                        $q->where('state_code', $stateCode)
                            ->whereNull('course_id');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNull('state_code')
                            ->whereNull('course_id');
                    });
            })
            ->orderByRaw('CASE 
                WHEN course_id IS NOT NULL THEN 1 
                WHEN state_code IS NOT NULL THEN 2 
                ELSE 3 
            END')
            ->orderBy('display_order')
            ->first();
    }

    public function getResponsesCount(): int
    {
        return $this->responses()->count();
    }

    public function getCompletedResponsesCount(): int
    {
        return $this->responses()->whereNotNull('completed_at')->count();
    }

    public function getCompletionRate(): float
    {
        $total = $this->getResponsesCount();
        if ($total === 0) {
            return 0;
        }

        return ($this->getCompletedResponsesCount() / $total) * 100;
    }
}
