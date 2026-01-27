<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissouriCourseStructure extends Model
{
    protected $fillable = [
        'chapter_number',
        'chapter_title',
        'content',
        'quiz_questions_count',
        'passing_score',
        'time_requirement_minutes',
    ];

    const MISSOURI_CHAPTERS = [
        1 => 'Missouri Traffic Laws',
        2 => 'Road Signs and Signals',
        3 => 'Defensive Driving Techniques',
        4 => 'Highway and Interstate Driving',
        5 => 'Night Driving Safety',
        6 => 'Vehicle Maintenance and Safety',
        7 => 'DUI and Substance Abuse Laws',
        8 => 'Weather and Road Conditions',
        9 => 'Emergency Procedures',
        10 => 'Sharing the Road',
        11 => 'Missouri Point System and Penalties',
    ];

    public function quizQuestions()
    {
        return $this->hasMany(MissouriQuizBank::class, 'chapter_id');
    }
}
