<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseChapter extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'content',
        'video_url',
        'order_index',
        'duration',
        'required_min_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course()
    {
        // This relationship is now flexible since we removed the foreign key constraint
        // The actual course could be in either 'courses' or 'florida_courses' table
        // We'll need to determine which table to use based on context
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }

    public function getCourseFromAnyTable()
    {
        // Try to find the course in the regular courses table first
        $course = \DB::table('courses')->where('id', $this->course_id)->first();
        
        if (!$course) {
            // If not found, try florida_courses table
            $course = \DB::table('florida_courses')->where('id', $this->course_id)->first();
        }
        
        return $course;
    }

    public function questions()
    {
        return $this->hasMany(ChapterQuestion::class, 'chapter_id');
    }
}
