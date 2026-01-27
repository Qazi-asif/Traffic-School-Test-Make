<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissouriQuizBank extends Model
{
    protected $fillable = [
        'chapter',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_answer',
        'difficulty',
        'is_final_exam',
    ];

    protected $casts = [
        'is_final_exam' => 'boolean',
    ];

    const DIFFICULTY_LEVELS = ['easy', 'medium', 'hard'];
}
