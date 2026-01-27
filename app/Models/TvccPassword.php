<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TvccPassword extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'password',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /**
     * Get the current TVCC password.
     */
    public static function current(): ?string
    {
        $record = self::orderBy('updated_at', 'desc')->first();
        return $record?->password;
    }

    /**
     * Update the TVCC password.
     */
    public static function updatePassword(string $password): void
    {
        self::query()->delete(); // Remove old passwords
        self::create([
            'password' => $password,
            'updated_at' => now(),
        ]);
    }
}
