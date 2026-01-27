<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLegalConsent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'document_id',
        'consent_given',
        'ip_address',
        'user_agent',
        'consented_at',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
        'consented_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'document_id');
    }
}
