<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloridaDataExport extends Model
{
    protected $fillable = [
        'user_id',
        'export_type',
        'status',
        'file_path',
        'requested_at',
        'completed_at',
        'florida_retention_period',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
