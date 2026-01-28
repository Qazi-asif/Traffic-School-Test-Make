<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StateStamp extends Model
{
    protected $fillable = [
        'state_code',
        'stamp_name',
        'image_path',
        'image_url',
        'width',
        'height',
        'is_active',
        'description',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }

        if ($this->image_path) {
            return Storage::url($this->image_path);
        }

        return null;
    }

    /**
     * Scope for active stamps
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific state
     */
    public function scopeForState($query, string $stateCode)
    {
        return $query->where('state_code', strtoupper($stateCode));
    }

    /**
     * Get default state stamps
     */
    public static function getDefaultStamps(): array
    {
        return [
            'FL' => [
                'stamp_name' => 'Florida State Seal',
                'description' => 'Official Florida state seal for certificates',
                'width' => 80,
                'height' => 80,
            ],
            'MO' => [
                'stamp_name' => 'Missouri State Seal',
                'description' => 'Official Missouri state seal for certificates',
                'width' => 80,
                'height' => 80,
            ],
            'TX' => [
                'stamp_name' => 'Texas State Seal',
                'description' => 'Official Texas state seal for certificates',
                'width' => 80,
                'height' => 80,
            ],
            'DE' => [
                'stamp_name' => 'Delaware State Seal',
                'description' => 'Official Delaware state seal for certificates',
                'width' => 80,
                'height' => 80,
            ],
        ];
    }

    /**
     * Create default state stamps
     */
    public static function createDefaultStamps(): void
    {
        $defaultStamps = self::getDefaultStamps();

        foreach ($defaultStamps as $stateCode => $stampData) {
            self::updateOrCreate(
                [
                    'state_code' => $stateCode,
                    'stamp_name' => $stampData['stamp_name'],
                ],
                array_merge($stampData, [
                    'is_active' => true,
                ])
            );
        }
    }
}