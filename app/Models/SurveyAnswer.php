<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAnswer extends Model
{
    protected $fillable = [
        'survey_response_id',
        'survey_question_id',
        'answer_text',
        'answer_rating',
        'answer_option',
    ];

    protected $casts = [
        'answer_rating' => 'integer',
    ];

    // Relationships
    public function surveyResponse(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class);
    }

    public function surveyQuestion(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class);
    }

    // Methods
    public function getFormattedAnswer(): string
    {
        $question = $this->surveyQuestion;

        return match ($question->question_type) {
            'rating', 'scale_1_5', 'scale_1_10' => (string) $this->answer_rating,
            'multiple_choice', 'yes_no' => $this->answer_option ?? '',
            'text' => $this->answer_text ?? '',
            default => '',
        };
    }
}
