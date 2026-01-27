<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaSchool extends Model
{
    protected $fillable = [
        'school_id',
        'school_name',
        'address',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
