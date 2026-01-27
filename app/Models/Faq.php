<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'category',
        'question',
        'answer',
        'order',
        'is_active',
        'language',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
