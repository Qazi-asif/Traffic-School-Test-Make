<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateQrCode extends Model
{
    protected $fillable = [
        'florida_certificate_id',
        'qr_code_data',
        'qr_code_image',
        'verification_url',
        'scanned_count',
    ];

    public function certificate()
    {
        return $this->belongsTo(FloridaCertificate::class, 'florida_certificate_id');
    }
}
