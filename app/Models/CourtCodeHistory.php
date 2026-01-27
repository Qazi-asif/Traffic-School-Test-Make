<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourtCodeHistory extends Model
{
    const UPDATED_AT = null;

    protected $table = 'court_code_history';

    protected $fillable = [
        'court_code_id',
        'action',
        'old_values',
        'new_values',
        'changed_by',
        'reason',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function courtCode(): BelongsTo
    {
        return $this->belongsTo(CourtCode::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
