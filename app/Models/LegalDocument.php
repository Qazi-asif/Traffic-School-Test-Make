<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalDocument extends Model
{
    protected $fillable = [
        'document_type',
        'title',
        'content',
        'version',
        'effective_date',
        'is_active',
        'requires_consent',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
        'requires_consent' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
