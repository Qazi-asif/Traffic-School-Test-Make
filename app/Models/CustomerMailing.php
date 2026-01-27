<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerMailing extends Model
{
    protected $fillable = [
        'enrollment_id', 'certificate_id', 'mailing_type', 'status',
        'address_line_1', 'address_line_2', 'city', 'state', 'zip_code',
        'tracking_number', 'carrier', 'printed_at', 'mailed_at', 'delivered_at',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
        'mailed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(FloridaCertificate::class, 'certificate_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePrinted($query)
    {
        return $query->where('status', 'printed');
    }

    public function scopeMailed($query)
    {
        return $query->where('status', 'mailed');
    }

    public function getFullAddress()
    {
        $address = $this->address_line_1;
        if ($this->address_line_2) {
            $address .= "\n".$this->address_line_2;
        }
        $address .= "\n{$this->city}, {$this->state} {$this->zip_code}";

        return $address;
    }
}
