<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloridaComplianceCheck extends Model
{
    protected $fillable = [
        'check_type',
        'check_name',
        'status',
        'details',
        'performed_by',
        'performed_at',
        'next_due_date',
    ];

    protected $casts = [
        'details' => 'array',
        'performed_at' => 'datetime',
        'next_due_date' => 'date',
    ];

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
