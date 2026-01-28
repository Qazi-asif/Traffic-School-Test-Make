<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CertificateVerificationLog extends Model
{
    protected $fillable = [
        'certificate_id',
        'certificate_type',
        'verified_by',
        'verification_method',
        'verification_result',
        'verification_data',
        'verified_at',
    ];

    protected $casts = [
        'verification_data' => 'array',
        'verified_at' => 'datetime',
    ];

    // Verification methods
    const METHOD_WEB = 'web';
    const METHOD_API = 'api';
    const METHOD_PHONE = 'phone';
    const METHOD_EMAIL = 'email';

    // Verification results
    const RESULT_VALID = 'valid';
    const RESULT_INVALID = 'invalid';
    const RESULT_EXPIRED = 'expired';
    const RESULT_REVOKED = 'revoked';

    public function certificate(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for valid verifications
     */
    public function scopeValid($query)
    {
        return $query->where('verification_result', self::RESULT_VALID);
    }

    /**
     * Scope for invalid verifications
     */
    public function scopeInvalid($query)
    {
        return $query->where('verification_result', self::RESULT_INVALID);
    }

    /**
     * Scope for web verifications
     */
    public function scopeWeb($query)
    {
        return $query->where('verification_method', self::METHOD_WEB);
    }

    /**
     * Scope for API verifications
     */
    public function scopeApi($query)
    {
        return $query->where('verification_method', self::METHOD_API);
    }

    /**
     * Get verification statistics
     */
    public static function getVerificationStats(array $filters = []): array
    {
        $query = static::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('verified_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('verified_at', '<=', $filters['date_to']);
        }

        if (isset($filters['method'])) {
            $query->where('verification_method', $filters['method']);
        }

        $total = $query->count();
        $valid = $query->where('verification_result', self::RESULT_VALID)->count();
        $invalid = $query->where('verification_result', self::RESULT_INVALID)->count();

        return [
            'total_verifications' => $total,
            'valid_verifications' => $valid,
            'invalid_verifications' => $invalid,
            'success_rate' => $total > 0 ? round(($valid / $total) * 100, 2) : 0,
        ];
    }
}