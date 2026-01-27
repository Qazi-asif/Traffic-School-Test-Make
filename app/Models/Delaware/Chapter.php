<?php

namespace App\Models\Delaware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $table = 'delaware_chapters';

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'order_index',
        'duration_minutes',
        'is_active',
        'enforce_minimum_time',
        'quiz_rotation_set',
        'has_interactive_content',
        'interactive_content_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'enforce_minimum_time' => 'boolean',
        'has_interactive_content' => 'boolean',
        'order_index' => 'integer',
        'duration_minutes' => 'integer',
        'quiz_rotation_set' => 'string',
    ];

    const QUIZ_ROTATION_SETS = [
        'A' => 'Rotation Set A',
        'B' => 'Rotation Set B',
        'C' => 'Rotation Set C',
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

    public function scopeWithInteractiveContent($query)
    {
        return $query->where('has_interactive_content', true);
    }

    public function scopeByRotationSet($query, $set)
    {
        return $query->where('quiz_rotation_set', $set);
    }

    public function getRotatedQuizQuestions($studentId, $limit = 10)
    {
        // Delaware-specific quiz rotation logic
        $course = $this->course;
        
        if (!$course->quiz_rotation_enabled) {
            return $this->quizQuestions()
                ->active()
                ->orderBy('order_index')
                ->limit($limit)
                ->get();
        }
        
        // Use student ID and chapter ID to determine rotation set
        $rotationSets = array_keys(self::QUIZ_ROTATION_SETS);
        $setIndex = ($studentId + $this->id) % count($rotationSets);
        $selectedSet = $rotationSets[$setIndex];
        
        return $this->quizQuestions()
            ->active()
            ->where('quiz_rotation_set', $selectedSet)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}