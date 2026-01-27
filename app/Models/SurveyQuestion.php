<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'options',
        'is_required',
        'display_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    // Accessors
    protected function optionsArray(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $this->options ?? [],
        );
    }

    // Methods
    public function formatForDisplay(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->question_text,
            'type' => $this->question_type,
            'options' => $this->optionsArray,
            'required' => $this->is_required,
        ];
    }

    public function getQuestionTypeLabel(): string
    {
        return match ($this->question_type) {
            'rating' => 'Rating',
            'multiple_choice' => 'Multiple Choice',
            'text' => 'Text',
            'yes_no' => 'Yes/No',
            'scale_1_5' => 'Scale (1-5)',
            'scale_1_10' => 'Scale (1-10)',
            default => 'Unknown',
        };
    }
}
