<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NevadaCourse extends Model
{
    protected $fillable = [
        'course_id',
        'nevada_course_code',
        'course_type',
        'approved_date',
        'expiration_date',
        'approval_number',
        'required_hours',
        'max_completion_days',
        'is_active',
    ];

    protected $casts = [
        'approved_date' => 'date',
        'expiration_date' => 'date',
        'required_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expiration_date')
                    ->orWhere('expiration_date', '>=', now());
            });
    }
}
