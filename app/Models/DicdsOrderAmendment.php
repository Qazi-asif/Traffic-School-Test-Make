<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DicdsOrderAmendment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'original_certificate_count',
        'amended_certificate_count',
        'original_total_amount',
        'amended_total_amount',
        'amended_by',
        'amendment_reason',
        'amended_at',
    ];

    protected $casts = [
        'amended_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DicdsCertificateOrder::class, 'order_id');
    }

    public function amendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'amended_by');
    }
}
