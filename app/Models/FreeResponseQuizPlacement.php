<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreeResponseQuizPlacement extends Model
{
    protected $table = 'free_response_quiz_placements';

    protected $fillable = [
        'course_id',
        'after_chapter_id',
        'quiz_title',
        'quiz_description',
        'is_active',
        'is_mandatory',
        'order_index',
        'use_random_selection',
        'questions_to_select',
        'total_questions_in_pool',
        'enforce_24hour_grading',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
        'use_random_selection' => 'boolean',
        'enforce_24hour_grading' => 'boolean',
        'questions_to_select' => 'integer',
        'total_questions_in_pool' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function afterChapter()
    {
        return $this->belongsTo(Chapter::class, 'after_chapter_id');
    }

    public function questions()
    {
        return $this->hasMany(FreeResponseQuestion::class, 'course_id', 'course_id')
                    ->where('placement_id', $this->id);
    }
}