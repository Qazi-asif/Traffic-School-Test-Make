<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaInstructor extends Model
{
    protected $fillable = [
        'instructor_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
