<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterQuizResult extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'total_questions',
        'correct_answers',
        'wrong_answers',
        'percentage',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
        'percentage' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public static function calculateUserQuizAverage($userId, $courseId)
    {
        // Get all chapters for the course
        $chapters = Chapter::where('course_id', $courseId)->pluck('id');
        
        // Get quiz results for this user and these chapters
        $results = self::where('user_id', $userId)
            ->whereIn('chapter_id', $chapters)
            ->get();
        
        if ($results->isEmpty()) {
            return null;
        }
        
        // Calculate average percentage
        $average = $results->avg('percentage');
        
        return round($average, 2);
    }
}