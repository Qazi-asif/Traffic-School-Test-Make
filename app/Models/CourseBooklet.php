<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CourseBooklet extends Model
{
    protected $fillable = [
        'course_id',
        'version',
        'title',
        'state_code',
        'file_path',
        'page_count',
        'file_size',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'page_count' => 'integer',
        'file_size' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the course (from any table)
     */
    public function getCourseAttribute()
    {
        // Try main courses table first
        $course = Course::find($this->course_id);

        // If not found, try Florida courses
        if (! $course && \Schema::hasTable('florida_courses')) {
            $course = \App\Models\FloridaCourse::find($this->course_id);
        }

        return $course;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BookletOrder::class, 'booklet_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generateForStudent(User $student): string
    {
        $service = app(\App\Services\BookletService::class);

        return $service->generatePersonalizedBooklet(
            $this,
            $student
        );
    }

    public function getDownloadUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForState($query, string $stateCode)
    {
        return $query->where('state_code', $stateCode)
            ->orWhereNull('state_code');
    }
}
