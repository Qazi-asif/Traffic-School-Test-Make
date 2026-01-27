<?php

namespace App\Models\Texas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $table = 'texas_quiz_questions';

    protected $fillable = [
        'course_id',
        'chapter_id',
        'quiz_id',
        'question_text',
        'question_type',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_answer',
        'explanation',
        'points',
        'order_index',
        'is_active',
        'difficulty_level',
        'tdlr_approved',
        'image_url',
    ];

    protected $casts = [
        'points' => 'integer',
        'order_index' => 'integer',
        'is_active' => 'boolean',
        'tdlr_approved' => 'boolean',
        'difficulty_level' => 'string',
    ];

    const QUESTION_TYPES = [
        'multiple_choice' => 'Multiple Choice',
        'true_false' => 'True/False',
        'fill_blank' => 'Fill in the Blank',
        'image_based' => 'Image-Based Question',
    ];

    const DIFFICULTY_LEVELS = [
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(ChapterQuiz::class, 'quiz_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTdlrApproved($query)
    {
        return $query->where('tdlr_approved', true);
    }

    public function scopeWithImage($query)
    {
        return $query->whereNotNull('image_url');
    }

    public function getOptionsAttribute()
    {
        $options = [];
        
        if ($this->option_a) $options['A'] = $this->option_a;
        if ($this->option_b) $options['B'] = $this->option_b;
        if ($this->option_c) $options['C'] = $this->option_c;
        if ($this->option_d) $options['D'] = $this->option_d;
        if ($this->option_e) $options['E'] = $this->option_e;
        
        return $options;
    }

    public function isCorrectAnswer($answer)
    {
        return strtoupper($answer) === strtoupper($this->correct_answer);
    }

    public function hasImage()
    {
        return !empty($this->image_url);
    }
}