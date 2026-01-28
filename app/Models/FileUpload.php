<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'file_type',
        'state',
        'course_id',
        'chapter_id',
        'uploaded_by',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function uploader()
    {
        return $this->belongsTo(AdminUser::class, 'uploaded_by');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function floridaCourse()
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }

    public function scopeDocuments($query)
    {
        return $query->where('file_type', 'document');
    }

    public function scopeImages($query)
    {
        return $query->where('file_type', 'image');
    }

    // Helper methods
    public function getUrl()
    {
        return Storage::url($this->file_path);
    }

    public function getFullPath()
    {
        return Storage::path($this->file_path);
    }

    public function exists()
    {
        return Storage::exists($this->file_path);
    }

    public function delete()
    {
        // Delete the physical file
        if ($this->exists()) {
            Storage::delete($this->file_path);
        }

        // Delete the database record
        return parent::delete();
    }

    public function getFormattedSize()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isVideo()
    {
        return $this->file_type === 'video';
    }

    public function isDocument()
    {
        return $this->file_type === 'document';
    }

    public function isImage()
    {
        return $this->file_type === 'image';
    }
}