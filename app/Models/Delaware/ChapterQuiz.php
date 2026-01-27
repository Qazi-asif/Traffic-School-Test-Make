<?php

namespace App\Models\Delaware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChapterQuiz extends Model
{
    protected $table = 'delaware_chapter_quizzes';

    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'passing_score',
        'time_limit_minutes',
        'max_attempts',
        'randomize_questions',
        'show_correct_answers',
        'is_active',
        'rotation_enabled',
        'rotation_sets',
    ];

    protected $casts = [
        'passing_score' => 'integer',
        'time_limit_minutes' => 'integer',
        'max_attempts' => 'integer',
        'randomize_questions' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_active' => 'boolean',
        'rotation_enabled' => 'boolean',
        'rotation_sets' => 'array',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'quiz_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRotationEnabled($query)
    {
        return $query->where('rotation_enabled', true);
    }

    public function getQuestionsForAttempt($limit = null, $rotationSet = null)
    {
        $query = $this->questions()->active();
        
        // Delaware-specific rotation logic
        if ($this->rotation_enabled && $rotationSet) {
            $query->where('quiz_rotation_set', $rotationSet);
        }
        
        if ($this->randomize_questions) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('order_index');
        }
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    public function getAvailableRotationSets()
    {
        if (!$this->rotation_enabled) {
            return ['default'];
        }
        
        return $this->rotation_sets ?: ['A', 'B', 'C'];
    }

    public function getQuestionsForRotationSet($set, $limit = 10)
    {
        return $this->questions()
            ->active()
            ->where('quiz_rotation_set', $set)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}