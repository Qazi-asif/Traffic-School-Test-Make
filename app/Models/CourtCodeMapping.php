<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourtCodeMapping extends Model
{
    protected $fillable = [
        'court_code_id',
        'external_system',
        'external_code',
        'external_name',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function courtCode(): BelongsTo
    {
        return $this->belongsTo(CourtCode::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeForSystem($query, string $system)
    {
        return $query->where('external_system', $system);
    }
}
