<?php

namespace App\Models\Texas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChapterQuiz extends Model
{
    protected $table = 'texas_chapter_quizzes';

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
        'requires_proctoring',
        'video_proctoring_enabled',
    ];

    protected $casts = [
        'passing_score' => 'integer',
        'time_limit_minutes' => 'integer',
        'max_attempts' => 'integer',
        'randomize_questions' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_active' => 'boolean',
        'requires_proctoring' => 'boolean',
        'video_proctoring_enabled' => 'boolean',
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

    public function scopeRequiresProctoring($query)
    {
        return $query->where('requires_proctoring', true);
    }

    public function getQuestionsForAttempt($limit = null)
    {
        $query = $this->questions()->active();
        
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

    public function requiresVideoProctoring()
    {
        return $this->requires_proctoring && $this->video_proctoring_enabled;
    }
}