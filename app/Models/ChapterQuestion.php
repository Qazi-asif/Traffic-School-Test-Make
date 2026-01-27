<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterQuestion extends Model
{
    protected $fillable = [
        'chapter_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'order_index',
        'quiz_set',
    ];

    protected $casts = [
        'points' => 'integer',
        'order_index' => 'integer',
        'quiz_set' => 'integer',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
