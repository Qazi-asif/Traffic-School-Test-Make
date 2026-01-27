<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReasonAttending extends Model
{
    protected $table = 'reason_attending';

    protected $fillable = [
        'code',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
