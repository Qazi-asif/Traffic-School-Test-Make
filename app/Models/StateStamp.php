<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateStamp extends Model
{
    protected $fillable = [
        'state_code',
        'state_name',
        'logo_path',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return asset('storage/'.$this->logo_path);
        }

        return null;
    }
}
