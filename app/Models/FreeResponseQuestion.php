<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreeResponseQuestion extends Model
{
    protected $table = 'free_response_questions';

    protected $fillable = [
        'course_id',
        'placement_id',
        'question_text',
        'order_index',
        'sample_answer',
        'grading_rubric',
        'points',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function answers()
    {
        return $this->hasMany(FreeResponseAnswer::class, 'question_id');
    }

    public function placement()
    {
        return $this->belongsTo(FreeResponseQuizPlacement::class, 'placement_id');
    }
}
