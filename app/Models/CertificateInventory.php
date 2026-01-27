<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateInventory extends Model
{
    protected $table = 'certificate_inventory';

    protected $fillable = [
        'course_type',
        'delivery_type',
        'total_ordered',
        'total_used',
        'available_count',
        'provider_hold',
        'school_hold',
        'last_updated',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
    ];
}
