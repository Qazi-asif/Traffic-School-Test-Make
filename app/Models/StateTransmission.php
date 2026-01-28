<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StateTransmission extends Model
{
    protected $fillable = [
        'certificate_id',
        'enrollment_id',
        'state',
        'system',
        'status',
        'payload_json',
        'response_code',
        'response_message',
        'sent_at',
        'retry_count',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    // Transmission statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_FAILED = 'failed';

    // State systems
    const SYSTEM_DICDS = 'DICDS';      // Florida
    const SYSTEM_DOR = 'DOR';          // Missouri
    const SYSTEM_TDLR = 'TDLR';        // Texas
    const SYSTEM_DMV = 'DMV';          // Delaware

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    /**
     * Scope for pending transmissions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for successful transmissions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope for failed transmissions
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_ERROR, self::STATUS_FAILED]);
    }

    /**
     * Scope for specific state
     */
    public function scopeForState($query, string $state)
    {
        return $query->where('state', strtoupper($state));
    }

    /**
     * Scope for specific system
     */
    public function scopeForSystem($query, string $system)
    {
        return $query->where('system', strtoupper($system));
    }

    /**
     * Check if transmission can be retried
     */
    public function canRetry(): bool
    {
        return $this->status !== self::STATUS_SUCCESS && 
               $this->retry_count < 3;
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Pending';
            case self::STATUS_PROCESSING:
                return 'Processing';
            case self::STATUS_SUCCESS:
                return 'Success';
            case self::STATUS_ERROR:
                return 'Error';
            case self::STATUS_FAILED:
                return 'Failed';
            default:
                return ucfirst($this->status);
        }
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_PROCESSING:
                return 'info';
            case self::STATUS_SUCCESS:
                return 'success';
            case self::STATUS_ERROR:
            case self::STATUS_FAILED:
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get system display name
     */
    public function getSystemDisplayNameAttribute(): string
    {
        switch ($this->system) {
            case self::SYSTEM_DICDS:
                return 'Florida DICDS';
            case self::SYSTEM_DOR:
                return 'Missouri DOR';
            case self::SYSTEM_TDLR:
                return 'Texas TDLR';
            case self::SYSTEM_DMV:
                return 'Delaware DMV';
            default:
                return $this->system;
        }
    }

    /**
     * Get transmission statistics
     */
    public static function getStatistics(array $filters = []): array
    {
        $query = static::query();

        // Apply filters
        if (isset($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (isset($filters['system'])) {
            $query->where('system', $filters['system']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $successful = $query->where('status', self::STATUS_SUCCESS)->count();
        $pending = $query->where('status', self::STATUS_PENDING)->count();
        $failed = $query->whereIn('status', [self::STATUS_ERROR, self::STATUS_FAILED])->count();

        return [
            'total_transmissions' => $total,
            'successful_transmissions' => $successful,
            'pending_transmissions' => $pending,
            'failed_transmissions' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get state breakdown
     */
    public static function getStateBreakdown(): array
    {
        return static::selectRaw('state, count(*) as count')
            ->groupBy('state')
            ->pluck('count', 'state')
            ->toArray();
    }

    /**
     * Get system breakdown
     */
    public static function getSystemBreakdown(): array
    {
        return static::selectRaw('system, count(*) as count')
            ->groupBy('system')
            ->pluck('count', 'system')
            ->toArray();
    }

    /**
     * Get recent failed transmissions
     */
    public static function getRecentFailures(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::with(['certificate', 'enrollment.user'])
            ->whereIn('status', [self::STATUS_ERROR, self::STATUS_FAILED])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }
}