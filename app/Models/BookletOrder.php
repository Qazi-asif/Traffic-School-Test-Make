<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookletOrder extends Model
{
    protected $fillable = [
        'enrollment_id',
        'booklet_id',
        'status',
        'format',
        'file_path',
        'personalization_data',
        'printed_at',
        'shipped_at',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'personalization_data' => 'array',
        'printed_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function booklet(): BelongsTo
    {
        return $this->belongsTo(CourseBooklet::class, 'booklet_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopePrintQueue($query)
    {
        return $query->whereIn('status', ['ready', 'printed'])
            ->whereIn('format', ['print_mail', 'print_pickup']);
    }

    public function generate(): void
    {
        $service = app(\App\Services\BookletService::class);
        $service->processOrder($this);
    }

    public function markPrinted(): void
    {
        $this->update([
            'status' => 'printed',
            'printed_at' => now(),
        ]);
    }

    public function markShipped(?string $trackingNumber = null): void
    {
        $this->update([
            'status' => 'shipped',
            'shipped_at' => now(),
            'tracking_number' => $trackingNumber,
        ]);
    }

    public function markDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
        ]);
    }

    public function markFailed(?string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }

    public function getDownloadUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    public function isDownloadable(): bool
    {
        return $this->status === 'ready' &&
               $this->format === 'pdf_download' &&
               $this->file_path;
    }
}
