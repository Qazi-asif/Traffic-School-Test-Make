<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityQuestion extends Model
{
    protected $fillable = [
        'question_key',
        'question_text',
        'answer_type',
        'help_text',
        'is_active',
        'order_index',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    public function getFullQuestionAttribute()
    {
        $question = $this->question_text;
        if ($this->help_text) {
            $question .= ' ' . $this->help_text;
        }
        return $question;
    }
}
