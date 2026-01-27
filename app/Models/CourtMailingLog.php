<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourtMailingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'court_mailing_id', 'action', 'old_status', 'new_status',
        'notes', 'performed_by', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function courtMailing(): BelongsTo
    {
        return $this->belongsTo(CourtMailing::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
