<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourtCode extends Model
{
    protected $fillable = [
        'court_id',
        'code_type',
        'code_value',
        'code_name',
        'is_active',
        'effective_date',
        'expiration_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'expiration_date' => 'date',
    ];

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(CourtCodeMapping::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(CourtCodeHistory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('code_type', $type);
    }

    public function scopeEffective($query, ?Carbon $date = null)
    {
        $date = $date ?? now();

        return $query->where(function ($q) use ($date) {
            $q->whereNull('effective_date')
                ->orWhere('effective_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('expiration_date')
                ->orWhere('expiration_date', '>=', $date);
        });
    }

    public function scopeForState($query, string $stateCode)
    {
        return $query->whereHas('court', function ($q) use ($stateCode) {
            $q->where('state', $stateCode);
        });
    }

    public function isEffective(?Carbon $date = null): bool
    {
        $date = $date ?? now();

        if ($this->effective_date && $this->effective_date->isAfter($date)) {
            return false;
        }

        if ($this->expiration_date && $this->expiration_date->isBefore($date)) {
            return false;
        }

        return true;
    }

    public function getExternalCode(string $system): ?string
    {
        return $this->mappings()
            ->where('external_system', $system)
            ->where('is_verified', true)
            ->first()
            ?->external_code;
    }

    public function logChange(string $action, ?array $oldValues, ?array $newValues, ?string $reason = null): void
    {
        $this->history()->create([
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_by' => auth()->id(),
            'reason' => $reason,
        ]);
    }
}
