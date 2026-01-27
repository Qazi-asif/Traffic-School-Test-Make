<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloridaOrderApproval extends Model
{
    protected $fillable = [
        'order_id',
        'approved_by_florida',
        'florida_approval_date',
        'florida_reference_number',
        'certificate_numbers_released',
        'release_date',
    ];

    protected $casts = [
        'approved_by_florida' => 'boolean',
        'florida_approval_date' => 'date',
        'certificate_numbers_released' => 'boolean',
        'release_date' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DicdsCertificateOrder::class, 'order_id');
    }
}
