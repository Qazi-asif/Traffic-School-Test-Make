<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMedia extends Model
{
    protected $fillable = [
        'course_id',
        'chapter_id',
        'title',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'order_index',
        'is_active',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
