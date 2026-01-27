<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateVerificationLog extends Model
{
    protected $fillable = [
        'certificate_id',
        'verified_by',
        'ip_address',
        'user_agent',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function certificate()
    {
        return $this->belongsTo(FloridaCertificate::class, 'certificate_id');
    }
}
