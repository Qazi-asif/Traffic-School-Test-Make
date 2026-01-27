<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloridaUserAccessibility extends Model
{
    protected $table = 'florida_user_accessibility';

    protected $fillable = [
        'user_id',
        'font_size',
        'high_contrast_mode',
        'reduced_animations',
        'screen_reader_optimized',
        'keyboard_navigation',
        'mobile_optimized',
    ];

    protected $casts = [
        'high_contrast_mode' => 'boolean',
        'reduced_animations' => 'boolean',
        'screen_reader_optimized' => 'boolean',
        'keyboard_navigation' => 'boolean',
        'mobile_optimized' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
