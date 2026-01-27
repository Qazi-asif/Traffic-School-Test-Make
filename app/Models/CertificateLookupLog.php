<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateLookupLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'searched_by',
        'search_type',
        'search_term',
        'results_count',
        'certificate_reprinted',
        'searched_at',
    ];

    protected $casts = [
        'certificate_reprinted' => 'boolean',
        'searched_at' => 'datetime',
    ];

    public function searcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'searched_by');
    }
}
