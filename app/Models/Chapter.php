<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Fallback fillable for compatibility
    protected $fillable = [
        'course_id',
        'course_table',
        'title',
        'content',
        'video_url',
        'order_index',
        'duration',
        'required_min_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        // Dynamically determine which course table to use
        if ($this->course_table === 'florida_courses') {
            return $this->belongsTo(FloridaCourse::class, 'course_id');
        }
        
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function questions(): HasMany
    {
        // Always use ChapterQuestion as the primary questions table
        return $this->hasMany(ChapterQuestion::class);
    }

    /**
     * Get questions from chapter_questions table (preferred)
     */
    public function chapterQuestions(): HasMany
    {
        return $this->hasMany(ChapterQuestion::class);
    }

    /**
     * Get questions from legacy questions table
     */
    public function legacyQuestions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get all questions from both tables (respects configuration)
     */
    public function allQuestions()
    {
        $disableLegacyTable = config('quiz.disable_legacy_questions_table', false);
        
        $chapterQuestions = $this->chapterQuestions()->get();
        
        // If legacy table is disabled, only return chapter_questions
        if ($disableLegacyTable) {
            return $chapterQuestions;
        }
        
        // If we have questions in the new table, use those
        if ($chapterQuestions->isNotEmpty()) {
            return $chapterQuestions;
        }
        
        // Otherwise, use legacy questions (if not disabled)
        return $this->legacyQuestions()->get();
    }

    /**
     * Check if chapter has any questions (respects configuration)
     */
    public function hasQuestions(): bool
    {
        $disableLegacyTable = config('quiz.disable_legacy_questions_table', false);
        
        // Always check chapter_questions first
        if ($this->chapterQuestions()->count() > 0) {
            return true;
        }
        
        // Only check legacy table if not disabled
        if (!$disableLegacyTable && $this->legacyQuestions()->count() > 0) {
            return true;
        }
        
        return false;
    }

    /**
     * Get the course regardless of which table it's in
     */
    public function getCourseAttribute()
    {
        if ($this->course_table === 'florida_courses') {
            return FloridaCourse::find($this->course_id);
        }
        
        return Course::find($this->course_id);
    }
}
