<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'user_id',
        'enrollment_id',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    // Scopes
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    // Methods
    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    public function calculateProgress(): float
    {
        $totalQuestions = $this->survey->questions()->where('is_required', true)->count();
        if ($totalQuestions === 0) {
            return 100;
        }

        $answeredQuestions = $this->answers()
            ->whereHas('surveyQuestion', function ($query) {
                $query->where('is_required', true);
            })
            ->count();

        return ($answeredQuestions / $totalQuestions) * 100;
    }

    public function markAsComplete(): void
    {
        $this->update([
            'completed_at' => now(),
        ]);
    }
}
