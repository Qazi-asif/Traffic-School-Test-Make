<?php

namespace App\Console\Commands;

use App\Models\UserCourseEnrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateEnrollmentCourseTables extends Command
{
    protected $signature = 'enrollments:update-course-tables';
    protected $description = 'Update existing enrollments to set correct course_table values';

    public function handle()
    {
        $this->info('Updating enrollment course_table values...');

        $updated = 0;
        $notFound = 0;

        UserCourseEnrollment::whereNull('course_table')
            ->orWhere('course_table', '')
            ->chunk(100, function ($enrollments) use (&$updated, &$notFound) {
                foreach ($enrollments as $enrollment) {
                    // Check if course exists in florida_courses
                    $floridaCourse = DB::table('florida_courses')->where('id', $enrollment->course_id)->exists();
                    $regularCourse = DB::table('courses')->where('id', $enrollment->course_id)->exists();
                    
                    if ($floridaCourse) {
                        $enrollment->update(['course_table' => 'florida_courses']);
                        $this->line("Updated enrollment {$enrollment->id} to florida_courses");
                        $updated++;
                    } elseif ($regularCourse) {
                        $enrollment->update(['course_table' => 'courses']);
                        $this->line("Updated enrollment {$enrollment->id} to courses");
                        $updated++;
                    } else {
                        $this->warn("Course not found for enrollment {$enrollment->id} (course_id: {$enrollment->course_id})");
                        $notFound++;
                    }
                }
            });

        $this->info("Migration complete! Updated: {$updated}, Not found: {$notFound}");
        
        return 0;
    }
}