<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DicdsOrderReceipt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'receipt_number',
        'receipt_data',
        'generated_by',
        'generated_at',
        'printed_at',
    ];

    protected $casts = [
        'receipt_data' => 'array',
        'generated_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DicdsCertificateOrder::class, 'order_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
