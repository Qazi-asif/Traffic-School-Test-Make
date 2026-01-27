<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    protected $fillable = [
        'name',
        'template_type',
        'html_content',
        'background_image',
        'font_family',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
