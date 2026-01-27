<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateDistribution extends Model
{
    protected $table = 'certificate_distribution';

    protected $fillable = [
        'certificate_order_id',
        'florida_school_id',
        'course_type',
        'amount_distributed',
        'distributed_by',
        'distributed_at',
    ];

    protected $casts = [
        'distributed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(DicdsCertificateOrder::class, 'certificate_order_id');
    }

    public function school()
    {
        return $this->belongsTo(FloridaSchool::class, 'florida_school_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}
