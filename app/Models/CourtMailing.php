<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourtMailing extends Model
{
    protected $fillable = [
        'enrollment_id', 'certificate_id', 'court_id', 'mailing_type', 'recipient_type',
        'status', 'address_line_1', 'address_line_2', 'city', 'state', 'zip_code',
        'tracking_number', 'carrier', 'shipping_method', 'weight_oz', 'postage_cost',
        'printed_at', 'mailed_at', 'delivered_at', 'returned_at', 'return_reason',
        'notes', 'printed_by', 'mailed_by', 'batch_id',
    ];

    protected $casts = [
        'weight_oz' => 'decimal:2',
        'postage_cost' => 'decimal:2',
        'printed_at' => 'datetime',
        'mailed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(FloridaCertificate::class, 'certificate_id');
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function mailedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mailed_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CourtMailingLog::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MailingBatch::class, 'batch_id');
    }

    // Scopes
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

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCourt($query, $courtId)
    {
        return $query->where('court_id', $courtId);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeForDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // Methods
    public function markPrinted($userId = null)
    {
        $this->update([
            'status' => 'printed',
            'printed_at' => now(),
            'printed_by' => $userId ?? auth()->id(),
        ]);
        $this->addLog('printed', 'pending', 'printed', $userId);
    }

    public function markMailed($trackingNumber = null, $userId = null)
    {
        $this->update([
            'status' => 'mailed',
            'mailed_at' => now(),
            'tracking_number' => $trackingNumber,
            'mailed_by' => $userId ?? auth()->id(),
        ]);
        $this->addLog('mailed', 'printed', 'mailed', $userId);
    }

    public function markDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        $this->addLog('delivered', 'mailed', 'delivered');
    }

    public function markReturned($reason)
    {
        $this->update([
            'status' => 'returned',
            'returned_at' => now(),
            'return_reason' => $reason,
        ]);
        $this->addLog('returned', 'mailed', 'returned');
    }

    public function addLog($action, $oldStatus = null, $newStatus = null, $userId = null, $notes = null)
    {
        return $this->logs()->create([
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'performed_by' => $userId ?? auth()->id(),
            'created_at' => now(),
        ]);
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
