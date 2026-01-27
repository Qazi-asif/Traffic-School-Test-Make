<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NevadaStudent extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'nevada_dmv_number',
        'court_case_number',
        'court_name',
        'citation_date',
        'due_date',
        'offense_code',
    ];

    protected $casts = [
        'citation_date' => 'date',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function isDueSoon(int $days = 7): bool
    {
        return $this->due_date && $this->due_date->diffInDays(now(), false) <= $days;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }
}
