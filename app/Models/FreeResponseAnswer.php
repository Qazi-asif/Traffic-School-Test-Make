<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreeResponseAnswer extends Model
{
    protected $table = 'free_response_answers';

    protected $fillable = [
        'question_id',
        'user_id',
        'enrollment_id',
        'answer_text',
        'word_count',
        'score',
        'feedback',
        'status',
        'submitted_at',
        'graded_at',
        'graded_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(FreeResponseQuestion::class, 'question_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
