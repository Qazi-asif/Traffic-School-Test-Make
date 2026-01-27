<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransmissionErrorCode extends Model
{
    protected $fillable = [
        'state',
        'error_code',
        'error_category',
        'technical_message',
        'user_friendly_message',
        'resolution_steps',
        'is_retryable',
    ];

    protected $casts = [
        'is_retryable' => 'boolean',
    ];

    /**
     * Get user-friendly message for an error code.
     */
    public static function getFriendlyMessage(string $state, string $errorCode): ?string
    {
        $error = self::where('state', $state)
            ->where('error_code', $errorCode)
            ->first();

        return $error?->user_friendly_message;
    }

    /**
     * Check if an error is retryable.
     */
    public static function isRetryable(string $state, string $errorCode): bool
    {
        $error = self::where('state', $state)
            ->where('error_code', $errorCode)
            ->first();

        return $error?->is_retryable ?? true;
    }
}
