<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolManagementLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'action',
        'performed_by',
        'details',
        'performed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'performed_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(FloridaSchool::class, 'school_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
