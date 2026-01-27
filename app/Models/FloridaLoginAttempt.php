<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaLoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'florida_compliance_check',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'florida_compliance_check' => 'boolean',
        'attempted_at' => 'datetime',
    ];
}
