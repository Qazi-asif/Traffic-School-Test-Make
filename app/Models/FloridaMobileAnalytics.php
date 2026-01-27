<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloridaMobileAnalytics extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'device_type',
        'course_id',
        'action',
        'mobile_performance_metric',
    ];

    protected $casts = [
        'mobile_performance_metric' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }
}
