<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MissouriSubmissionTracker extends Model
{
    protected $fillable = [
        'form_4444_id',
        'user_id',
        'completion_date',
        'submission_deadline',
        'days_remaining',
        'reminder_sent',
        'status',
        'notes',
    ];

    protected $casts = [
        'completion_date' => 'datetime',
        'submission_deadline' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    public function form4444()
    {
        return $this->belongsTo(MissouriForm4444::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateDaysRemaining()
    {
        if (! $this->submission_deadline) {
            return null;
        }

        $now = Carbon::now();
        $deadline = Carbon::parse($this->submission_deadline);

        return $deadline->diffInDays($now, false);
    }

    public function isExpired()
    {
        return $this->calculateDaysRemaining() < 0;
    }
}
