<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterTimer extends Model
{
    protected $fillable = [
        'chapter_id',
        'chapter_type',
        'required_time_minutes',
        'is_enabled',
        'allow_pause',
        'bypass_for_admin',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allow_pause' => 'boolean',
        'bypass_for_admin' => 'boolean',
        'required_time_minutes' => 'integer',
    ];

    public function chapter()
    {
        if ($this->chapter_type === 'florida_chapters') {
            return $this->belongsTo(Chapter::class, 'chapter_id');
        }

        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
