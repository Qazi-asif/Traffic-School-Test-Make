<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterProgress extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'started_at',
        'completed_at',
        'quiz_score',
        'quiz_passed',
        'time_spent_minutes',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'quiz_passed' => 'boolean',
    ];

    const STATUSES = ['not_started', 'in_progress', 'quiz_failed', 'completed'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(MissouriCourseStructure::class, 'chapter_id');
    }

    public function canTakeFinalExam()
    {
        // Must complete all 11 chapters
        $completedChapters = self::where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->count();

        return $completedChapters >= 11;
    }
}
