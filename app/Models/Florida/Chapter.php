<?php

namespace App\Models\Florida;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $table = 'florida_chapters';

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'order_index',
        'duration_minutes',
        'is_active',
        'enforce_minimum_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'enforce_minimum_time' => 'boolean',
        'order_index' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'chapter_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class, 'chapter_id');
    }

    public function chapterQuizzes(): HasMany
    {
        return $this->hasMany(ChapterQuiz::class, 'chapter_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }
}