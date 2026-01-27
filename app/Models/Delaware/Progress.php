<?php

namespace App\Models\Delaware;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $table = 'delaware_progress';

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'chapter_id',
        'started_at',
        'completed_at',
        'time_spent_minutes',
        'progress_percentage',
        'is_completed',
        'last_position',
        'quiz_passed',
        'quiz_attempts',
        'quiz_best_score',
        'rotation_set_used',
        'interactive_content_completed',
        'aggressive_driving_topics_covered',
        'insurance_discount_topics_covered',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent_minutes' => 'integer',
        'progress_percentage' => 'integer',
        'is_completed' => 'boolean',
        'quiz_passed' => 'boolean',
        'quiz_attempts' => 'integer',
        'quiz_best_score' => 'decimal:2',
        'interactive_content_completed' => 'boolean',
        'aggressive_driving_topics_covered' => 'boolean',
        'insurance_discount_topics_covered' => 'boolean',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeInteractiveContentCompleted($query)
    {
        return $query->where('interactive_content_completed', true);
    }

    public function scopeAggressiveDrivingCovered($query)
    {
        return $query->where('aggressive_driving_topics_covered', true);
    }

    public function scopeInsuranceDiscountCovered($query)
    {
        return $query->where('insurance_discount_topics_covered', true);
    }

    public function markAsStarted()
    {
        if (!$this->started_at) {
            $this->update(['started_at' => now()]);
        }
    }

    public function markAsCompleted()
    {
        $this->update([
            'completed_at' => now(),
            'is_completed' => true,
            'progress_percentage' => 100,
        ]);
    }

    public function markInteractiveContentCompleted()
    {
        $this->update(['interactive_content_completed' => true]);
    }

    public function updateProgress($percentage, $position = null)
    {
        $data = [
            'progress_percentage' => min(100, max(0, $percentage)),
        ];
        
        if ($position !== null) {
            $data['last_position'] = $position;
        }
        
        // Delaware-specific: Check interactive content requirement
        $chapter = $this->chapter;
        if ($chapter->has_interactive_content && !$this->interactive_content_completed) {
            $data['progress_percentage'] = min(75, $data['progress_percentage']);
        }
        
        if ($percentage >= 100 && (!$chapter->has_interactive_content || $this->interactive_content_completed)) {
            $data['completed_at'] = now();
            $data['is_completed'] = true;
        }
        
        $this->update($data);
    }

    public function canComplete()
    {
        $chapter = $this->chapter;
        
        if ($chapter->has_interactive_content && !$this->interactive_content_completed) {
            return false;
        }
        
        return $this->progress_percentage >= 100;
    }

    public function updateTopicCoverage($aggressiveDriving = false, $insuranceDiscount = false)
    {
        $data = [];
        
        if ($aggressiveDriving) {
            $data['aggressive_driving_topics_covered'] = true;
        }
        
        if ($insuranceDiscount) {
            $data['insurance_discount_topics_covered'] = true;
        }
        
        if (!empty($data)) {
            $this->update($data);
        }
    }
}