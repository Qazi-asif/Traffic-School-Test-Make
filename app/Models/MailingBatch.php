<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailingBatch extends Model
{
    protected $fillable = [
        'batch_number', 'batch_date', 'total_items', 'printed_count',
        'mailed_count', 'total_postage', 'status', 'notes', 'created_by', 'closed_at',
    ];

    protected $casts = [
        'batch_date' => 'date',
        'total_postage' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function courtMailings(): HasMany
    {
        return $this->hasMany(CourtMailing::class, 'batch_id', 'batch_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function open()
    {
        $this->update(['status' => 'open']);
    }

    public function startPrinting()
    {
        $this->update(['status' => 'printing']);
    }

    public function readyToMail()
    {
        $this->update(['status' => 'ready_to_mail']);
    }

    public function close()
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function addItem(CourtMailing $mailing)
    {
        $mailing->update(['batch_id' => $this->batch_number]);
        $this->increment('total_items');
    }
}
